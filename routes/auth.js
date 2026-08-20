const express = require('express');
const router = express.Router();
const bcrypt = require('bcryptjs');
const { query } = require('../config/db');
const { requireAdminAuth } = require('../middleware/auth');
const { strictLimiter } = require('../middleware/rateLimiter');

// Helper to log admin activity
async function logActivity(adminId, adminName, action, entityType, entityId, details, req) {
  try {
    const ip = req.headers['x-forwarded-for'] || req.socket.remoteAddress || '127.0.0.1';
    await query(
      `INSERT INTO admin_activity_logs (admin_id, admin_name, action, entity_type, entity_id, details, ip_address)
       VALUES (?, ?, ?, ?, ?, ?, ?)`,
      [adminId, adminName, action, entityType, entityId, details, ip]
    );
  } catch (err) {
    console.error('Failed to log admin activity:', err.message);
  }
}

// POST /api/auth/login
router.post('/login', strictLimiter, async (req, res) => {
  try {
    const { email, password } = req.body;

    if (!email || !password) {
      return res.status(400).json({
        success: false,
        message: 'Please provide both email and password.'
      });
    }

    const cleanEmail = email.trim().toLowerCase();
    const admins = await query('SELECT * FROM admins WHERE email = ? AND status = "active"', [cleanEmail]);

    if (admins.length === 0) {
      return res.status(401).json({
        success: false,
        message: 'Invalid email or password.'
      });
    }

    const admin = admins[0];
    const isMatch = await bcrypt.compare(password, admin.password_hash);

    if (!isMatch) {
      return res.status(401).json({
        success: false,
        message: 'Invalid email or password.'
      });
    }

    // Set user session
    req.session.adminUser = {
      id: admin.id,
      name: admin.name,
      email: admin.email,
      role: admin.role
    };

    await logActivity(admin.id, admin.name, 'LOGIN', 'auth', admin.id, 'Admin logged in successfully', req);

    return res.json({
      success: true,
      message: 'Login successful!',
      user: {
        id: admin.id,
        name: admin.name,
        email: admin.email,
        role: admin.role
      }
    });

  } catch (error) {
    console.error('Login error:', error);
    return res.status(500).json({
      success: false,
      message: 'Internal server error during login.'
    });
  }
});

// POST /api/auth/logout
router.post('/logout', (req, res) => {
  if (req.session.adminUser) {
    const { id, name } = req.session.adminUser;
    logActivity(id, name, 'LOGOUT', 'auth', id, 'Admin logged out', req);
  }

  req.session.destroy((err) => {
    if (err) {
      return res.status(500).json({
        success: false,
        message: 'Could not log out, please try again.'
      });
    }
    res.clearCookie('connect.sid');
    return res.json({
      success: true,
      message: 'Logged out successfully.'
    });
  });
});

// GET /api/auth/me
router.get('/me', (req, res) => {
  if (req.session && req.session.adminUser) {
    return res.json({
      success: true,
      authenticated: true,
      user: req.session.adminUser
    });
  }
  return res.json({
    success: true,
    authenticated: false,
    user: null
  });
});

// PUT /api/auth/profile
router.put('/profile', requireAdminAuth, async (req, res) => {
  try {
    const { name, email } = req.body;
    const adminId = req.session.adminUser.id;

    if (!name || !email) {
      return res.status(400).json({
        success: false,
        message: 'Name and email are required.'
      });
    }

    const cleanEmail = email.trim().toLowerCase();
    
    // Check if email taken by someone else
    const existing = await query('SELECT id FROM admins WHERE email = ? AND id != ?', [cleanEmail, adminId]);
    if (existing.length > 0) {
      return res.status(400).json({
        success: false,
        message: 'Email address is already in use by another administrator.'
      });
    }

    await query('UPDATE admins SET name = ?, email = ? WHERE id = ?', [name.trim(), cleanEmail, adminId]);
    
    req.session.adminUser.name = name.trim();
    req.session.adminUser.email = cleanEmail;

    await logActivity(adminId, name.trim(), 'UPDATE_PROFILE', 'admins', adminId, 'Updated profile details', req);

    return res.json({
      success: true,
      message: 'Profile updated successfully.',
      user: req.session.adminUser
    });
  } catch (error) {
    console.error('Update profile error:', error);
    return res.status(500).json({
      success: false,
      message: 'Internal server error.'
    });
  }
});

// PUT /api/auth/password
router.put('/password', requireAdminAuth, async (req, res) => {
  try {
    const { currentPassword, newPassword } = req.body;
    const adminId = req.session.adminUser.id;

    if (!currentPassword || !newPassword || newPassword.length < 6) {
      return res.status(400).json({
        success: false,
        message: 'New password must be at least 6 characters.'
      });
    }

    const admins = await query('SELECT password_hash FROM admins WHERE id = ?', [adminId]);
    if (admins.length === 0) {
      return res.status(404).json({ success: false, message: 'Admin not found.' });
    }

    const isMatch = await bcrypt.compare(currentPassword, admins[0].password_hash);
    if (!isMatch) {
      return res.status(400).json({
        success: false,
        message: 'Current password is incorrect.'
      });
    }

    const salt = await bcrypt.genSalt(10);
    const hash = await bcrypt.hash(newPassword, salt);

    await query('UPDATE admins SET password_hash = ? WHERE id = ?', [hash, adminId]);
    await logActivity(adminId, req.session.adminUser.name, 'CHANGE_PASSWORD', 'admins', adminId, 'Changed password', req);

    return res.json({
      success: true,
      message: 'Password changed successfully.'
    });
  } catch (error) {
    console.error('Password change error:', error);
    return res.status(500).json({
      success: false,
      message: 'Internal server error.'
    });
  }
});

module.exports = { router, logActivity };
