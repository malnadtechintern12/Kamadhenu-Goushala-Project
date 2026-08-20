// Admin authentication middleware using Express Session

function requireAdminAuth(req, res, next) {
  if (req.session && req.session.adminUser && req.session.adminUser.id) {
    return next();
  }
  
  // If API request, return JSON error
  if (req.originalUrl.startsWith('/api/')) {
    return res.status(401).json({
      success: false,
      message: 'Unauthorized access. Please log in as an administrator.'
    });
  }

  // Otherwise redirect to admin login page
  return res.redirect('/admin/index.html');
}

function requireSuperAdmin(req, res, next) {
  if (req.session && req.session.adminUser && req.session.adminUser.role === 'superadmin') {
    return next();
  }
  return res.status(403).json({
    success: false,
    message: 'Access forbidden. Superadmin privileges required.'
  });
}

module.exports = {
  requireAdminAuth,
  requireSuperAdmin
};
