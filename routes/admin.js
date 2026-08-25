const express = require('express');
const router = express.Router();
const path = require('path');
const fs = require('fs');
const { query } = require('../config/db');
const { requireAdminAuth } = require('../middleware/auth');
const upload = require('../middleware/upload');
const { logActivity } = require('./auth');

// Apply admin authentication to ALL admin API endpoints
router.use(requireAdminAuth);

// Helper to get image path from uploaded file or fallback URL
function getUploadedImagePath(file, fallbackUrl) {
  if (file) {
    return `/uploads/${file.filename}`;
  }
  return fallbackUrl || null;
}

// ----------------------------------------------------
// 1. DASHBOARD & STATS
// ----------------------------------------------------
router.get('/stats', async (req, res) => {
  try {
    const [cows] = await query('SELECT COUNT(*) as total, SUM(CASE WHEN status="Active" THEN 1 ELSE 0 END) as active FROM cows');
    const [donations] = await query('SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as total FROM donations WHERE payment_status = "Completed"');
    const [monthDonations] = await query(`
      SELECT COALESCE(SUM(amount), 0) as total
      FROM donations
      WHERE payment_status = "Completed"
      AND MONTH(created_at) = MONTH(CURRENT_DATE())
      AND YEAR(created_at) = YEAR(CURRENT_DATE())
    `);
    const [sponsors] = await query('SELECT COUNT(*) as count FROM sponsors');
    const [products] = await query('SELECT COUNT(*) as count FROM products');
    const [orders] = await query('SELECT COUNT(*) as count FROM orders');
    const [unreadMessages] = await query('SELECT COUNT(*) as count FROM contact_messages WHERE status = "New"');

    // Monthly donation stats for Chart.js (last 6 months)
    const monthlyChart = await query(`
      SELECT 
        DATE_FORMAT(created_at, '%b %Y') as label,
        COALESCE(SUM(amount), 0) as amount,
        COUNT(*) as donations_count
      FROM donations
      WHERE payment_status = "Completed"
      AND created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
      GROUP BY YEAR(created_at), MONTH(created_at), DATE_FORMAT(created_at, '%b %Y')
      ORDER BY YEAR(created_at) ASC, MONTH(created_at) ASC
    `);

    // Recent Donations
    const recentDonations = await query(`
      SELECT id, donation_number, donor_name, amount, seva_name, payment_status, created_at
      FROM donations
      ORDER BY id DESC LIMIT 5
    `);

    // Recent Messages
    const recentMessages = await query(`
      SELECT id, name, email, subject, status, created_at
      FROM contact_messages
      ORDER BY id DESC LIMIT 5
    `);

    // Recent Activity Logs
    const recentLogs = await query(`
      SELECT id, admin_name, action, entity_type, details, created_at
      FROM admin_activity_logs
      ORDER BY id DESC LIMIT 8
    `);

    return res.json({
      success: true,
      data: {
        total_cows: cows ? cows.total : 0,
        active_cows: cows ? cows.active : 0,
        total_donations_amount: donations ? donations.total : 0,
        total_donations_count: donations ? donations.count : 0,
        this_month_donations: monthDonations ? monthDonations.total : 0,
        total_sponsors: sponsors ? sponsors.count : 0,
        total_products: products ? products.count : 0,
        total_orders: orders ? orders.count : 0,
        unread_messages: unreadMessages ? unreadMessages.count : 0,
        monthly_chart: monthlyChart,
        recent_donations: recentDonations,
        recent_messages: recentMessages,
        recent_logs: recentLogs
      }
    });

  } catch (error) {
    console.error('Admin stats error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to retrieve dashboard stats.' });
  }
});

// ----------------------------------------------------
// 2. COW MANAGEMENT CRUD
// ----------------------------------------------------
router.get('/cows', async (req, res) => {
  try {
    const { breed_id, status, search } = req.query;
    let sql = `
      SELECT c.*, b.name as breed_name
      FROM cows c
      LEFT JOIN breeds b ON c.breed_id = b.id
      WHERE 1=1
    `;
    const params = [];

    if (breed_id) {
      sql += ' AND c.breed_id = ?';
      params.push(breed_id);
    }
    if (status) {
      sql += ' AND c.status = ?';
      params.push(status);
    }
    if (search) {
      sql += ' AND (c.name LIKE ? OR c.tag_number LIKE ?)';
      const s = `%${search.trim()}%`;
      params.push(s, s);
    }

    sql += ' ORDER BY c.id DESC';
    const cows = await query(sql, params);
    return res.json({ success: true, data: cows });
  } catch (error) {
    console.error('Admin get cows error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to retrieve cows.' });
  }
});

router.post('/cows', upload.single('image'), async (req, res) => {
  try {
    const { tag_number, name, breed_id, gender, dob, arrival_date, health_status, story, image_url, status } = req.body;

    if (!tag_number || !name) {
      return res.status(400).json({ success: false, message: 'Tag number and Cow name are required.' });
    }

    // Check tag uniqueness
    const existing = await query('SELECT id FROM cows WHERE tag_number = ?', [tag_number.trim()]);
    if (existing.length > 0) {
      return res.status(400).json({ success: false, message: 'Tag number already exists in database.' });
    }

    const finalImage = getUploadedImagePath(req.file, image_url);

    const result = await query(
      `INSERT INTO cows (
        tag_number, name, breed_id, gender, dob, arrival_date,
        health_status, story, image, status
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        tag_number.trim().toUpperCase(),
        name.trim(),
        breed_id ? parseInt(breed_id, 10) : null,
        gender || 'Female',
        dob || null,
        arrival_date || null,
        health_status || 'Healthy',
        story || null,
        finalImage,
        status || 'Active'
      ]
    );

    await logActivity(req.session.adminUser.id, req.session.adminUser.name, 'CREATE_COW', 'cows', result.insertId, `Added cow: ${name.trim()} (${tag_number})`, req);

    return res.json({ success: true, message: 'Cow record created successfully!', id: result.insertId });
  } catch (error) {
    console.error('Create cow error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to create cow record.' });
  }
});

router.put('/cows/:id', upload.single('image'), async (req, res) => {
  try {
    const cowId = parseInt(req.params.id, 10);
    const { tag_number, name, breed_id, gender, dob, arrival_date, health_status, story, image_url, status } = req.body;

    const existing = await query('SELECT * FROM cows WHERE id = ?', [cowId]);
    if (existing.length === 0) {
      return res.status(404).json({ success: false, message: 'Cow not found.' });
    }

    // Check tag uniqueness if changed
    if (tag_number && tag_number.trim().toUpperCase() !== existing[0].tag_number) {
      const duplicate = await query('SELECT id FROM cows WHERE tag_number = ? AND id != ?', [tag_number.trim().toUpperCase(), cowId]);
      if (duplicate.length > 0) {
        return res.status(400).json({ success: false, message: 'Tag number already in use by another cow.' });
      }
    }

    let finalImage = existing[0].image;
    if (req.file) {
      finalImage = `/uploads/${req.file.filename}`;
    } else if (image_url) {
      finalImage = image_url;
    }

    await query(
      `UPDATE cows SET
        tag_number = ?, name = ?, breed_id = ?, gender = ?,
        dob = ?, arrival_date = ?, health_status = ?, story = ?,
        image = ?, status = ?
       WHERE id = ?`,
      [
        tag_number ? tag_number.trim().toUpperCase() : existing[0].tag_number,
        name ? name.trim() : existing[0].name,
        breed_id ? parseInt(breed_id, 10) : null,
        gender || existing[0].gender,
        dob || existing[0].dob,
        arrival_date || existing[0].arrival_date,
        health_status || existing[0].health_status,
        story !== undefined ? story : existing[0].story,
        finalImage,
        status || existing[0].status,
        cowId
      ]
    );

    await logActivity(req.session.adminUser.id, req.session.adminUser.name, 'UPDATE_COW', 'cows', cowId, `Updated cow: ${name || existing[0].name}`, req);

    return res.json({ success: true, message: 'Cow record updated successfully!' });
  } catch (error) {
    console.error('Update cow error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to update cow record.' });
  }
});

router.delete('/cows/:id', async (req, res) => {
  try {
    const cowId = parseInt(req.params.id, 10);
    const existing = await query('SELECT name FROM cows WHERE id = ?', [cowId]);
    if (existing.length === 0) {
      return res.status(404).json({ success: false, message: 'Cow not found.' });
    }

    await query('DELETE FROM cows WHERE id = ?', [cowId]);
    await logActivity(req.session.adminUser.id, req.session.adminUser.name, 'DELETE_COW', 'cows', cowId, `Deleted cow: ${existing[0].name}`, req);

    return res.json({ success: true, message: 'Cow record deleted successfully.' });
  } catch (error) {
    console.error('Delete cow error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to delete cow record.' });
  }
});

// ----------------------------------------------------
// 3. BREED MANAGEMENT CRUD
// ----------------------------------------------------
router.get('/breeds', async (req, res) => {
  try {
    const breeds = await query(`
      SELECT b.*, COUNT(c.id) as total_cows
      FROM breeds b
      LEFT JOIN cows c ON b.id = c.breed_id
      GROUP BY b.id
      ORDER BY b.id ASC
    `);
    return res.json({ success: true, data: breeds });
  } catch (error) {
    console.error('Admin get breeds error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to retrieve breeds.' });
  }
});

router.post('/breeds', upload.single('image'), async (req, res) => {
  try {
    const { name, origin, description, milk_yield, characteristics, image_url, status } = req.body;
    if (!name) {
      return res.status(400).json({ success: false, message: 'Breed name is required.' });
    }

    const finalImage = getUploadedImagePath(req.file, image_url);

    const result = await query(
      `INSERT INTO breeds (name, origin, description, milk_yield, characteristics, image, status)
       VALUES (?, ?, ?, ?, ?, ?, ?)`,
      [name.trim(), origin || null, description || null, milk_yield || null, characteristics || null, finalImage, status || 'active']
    );

    await logActivity(req.session.adminUser.id, req.session.adminUser.name, 'CREATE_BREED', 'breeds', result.insertId, `Added breed: ${name}`, req);

    return res.json({ success: true, message: 'Breed added successfully!', id: result.insertId });
  } catch (error) {
    console.error('Create breed error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to create breed.' });
  }
});

router.put('/breeds/:id', upload.single('image'), async (req, res) => {
  try {
    const breedId = parseInt(req.params.id, 10);
    const { name, origin, description, milk_yield, characteristics, image_url, status } = req.body;

    const existing = await query('SELECT * FROM breeds WHERE id = ?', [breedId]);
    if (existing.length === 0) {
      return res.status(404).json({ success: false, message: 'Breed not found.' });
    }

    let finalImage = existing[0].image;
    if (req.file) {
      finalImage = `/uploads/${req.file.filename}`;
    } else if (image_url) {
      finalImage = image_url;
    }

    await query(
      `UPDATE breeds SET name = ?, origin = ?, description = ?, milk_yield = ?, characteristics = ?, image = ?, status = ? WHERE id = ?`,
      [
        name ? name.trim() : existing[0].name,
        origin !== undefined ? origin : existing[0].origin,
        description !== undefined ? description : existing[0].description,
        milk_yield !== undefined ? milk_yield : existing[0].milk_yield,
        characteristics !== undefined ? characteristics : existing[0].characteristics,
        finalImage,
        status || existing[0].status,
        breedId
      ]
    );

    await logActivity(req.session.adminUser.id, req.session.adminUser.name, 'UPDATE_BREED', 'breeds', breedId, `Updated breed: ${name || existing[0].name}`, req);

    return res.json({ success: true, message: 'Breed updated successfully!' });
  } catch (error) {
    console.error('Update breed error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to update breed.' });
  }
});

router.delete('/breeds/:id', async (req, res) => {
  try {
    const breedId = parseInt(req.params.id, 10);
    await query('DELETE FROM breeds WHERE id = ?', [breedId]);
    await logActivity(req.session.adminUser.id, req.session.adminUser.name, 'DELETE_BREED', 'breeds', breedId, `Deleted breed ID: ${breedId}`, req);
    return res.json({ success: true, message: 'Breed deleted successfully.' });
  } catch (error) {
    console.error('Delete breed error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to delete breed.' });
  }
});

// ----------------------------------------------------
// 4. SEVA SERVICES CRUD
// ----------------------------------------------------
router.get('/seva', async (req, res) => {
  try {
    const sevaList = await query('SELECT * FROM seva ORDER BY display_order ASC, id ASC');
    return res.json({ success: true, data: sevaList });
  } catch (error) {
    console.error('Admin get seva error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to retrieve seva.' });
  }
});

router.post('/seva', upload.single('image'), async (req, res) => {
  try {
    const { title, slug, short_desc, full_desc, suggested_amount, icon, image_url, whatsapp_number, display_order, status } = req.body;
    if (!title) {
      return res.status(400).json({ success: false, message: 'Seva title is required.' });
    }

    const cleanSlug = (slug || title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, ''));
    const finalImage = getUploadedImagePath(req.file, image_url);

    const result = await query(
      `INSERT INTO seva (title, slug, short_desc, full_desc, suggested_amount, icon, image, whatsapp_number, display_order, status)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        title.trim(),
        cleanSlug,
        short_desc || null,
        full_desc || null,
        parseFloat(suggested_amount || 1001),
        icon || 'bi-heart-fill',
        finalImage,
        whatsapp_number ? whatsapp_number.trim() : null,
        parseInt(display_order || 0, 10),
        status || 'active'
      ]
    );

    return res.json({ success: true, message: 'Seva service created successfully!', id: result.insertId });
  } catch (error) {
    console.error('Create seva error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to create seva.' });
  }
});

router.put('/seva/:id', upload.single('image'), async (req, res) => {
  try {
    const sevaId = parseInt(req.params.id, 10);
    const { title, slug, short_desc, full_desc, suggested_amount, icon, image_url, whatsapp_number, display_order, status } = req.body;

    const existing = await query('SELECT * FROM seva WHERE id = ?', [sevaId]);
    if (existing.length === 0) {
      return res.status(404).json({ success: false, message: 'Seva not found.' });
    }

    let finalImage = existing[0].image;
    if (req.file) {
      finalImage = `/uploads/${req.file.filename}`;
    } else if (image_url) {
      finalImage = image_url;
    }

    await query(
      `UPDATE seva SET title = ?, slug = ?, short_desc = ?, full_desc = ?, suggested_amount = ?, icon = ?, image = ?, whatsapp_number = ?, display_order = ?, status = ? WHERE id = ?`,
      [
        title ? title.trim() : existing[0].title,
        slug ? slug.trim() : existing[0].slug,
        short_desc !== undefined ? short_desc : existing[0].short_desc,
        full_desc !== undefined ? full_desc : existing[0].full_desc,
        suggested_amount !== undefined ? parseFloat(suggested_amount) : existing[0].suggested_amount,
        icon || existing[0].icon,
        finalImage,
        whatsapp_number !== undefined ? (whatsapp_number ? whatsapp_number.trim() : null) : existing[0].whatsapp_number,
        display_order !== undefined ? parseInt(display_order, 10) : existing[0].display_order,
        status || existing[0].status,
        sevaId
      ]
    );

    return res.json({ success: true, message: 'Seva service updated successfully!' });
  } catch (error) {
    console.error('Update seva error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to update seva.' });
  }
});

router.delete('/seva/:id', async (req, res) => {
  try {
    const sevaId = parseInt(req.params.id, 10);
    await query('DELETE FROM seva WHERE id = ?', [sevaId]);
    return res.json({ success: true, message: 'Seva service deleted successfully.' });
  } catch (error) {
    console.error('Delete seva error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to delete seva.' });
  }
});

// ----------------------------------------------------
// 5. DONATIONS & SPONSORSHIPS
// ----------------------------------------------------
router.get('/donations', async (req, res) => {
  try {
    const { payment_status, seva_id, start_date, end_date, search } = req.query;
    let sql = `
      SELECT d.*, s.title as seva_title
      FROM donations d
      LEFT JOIN seva s ON d.seva_id = s.id
      WHERE 1=1
    `;
    const params = [];

    if (payment_status) {
      sql += ' AND d.payment_status = ?';
      params.push(payment_status);
    }
    if (seva_id) {
      sql += ' AND d.seva_id = ?';
      params.push(seva_id);
    }
    if (start_date) {
      sql += ' AND DATE(d.created_at) >= ?';
      params.push(start_date);
    }
    if (end_date) {
      sql += ' AND DATE(d.created_at) <= ?';
      params.push(end_date);
    }
    if (search) {
      sql += ' AND (d.donor_name LIKE ? OR d.donor_email LIKE ? OR d.donation_number LIKE ? OR d.razorpay_payment_id LIKE ?)';
      const s = `%${search.trim()}%`;
      params.push(s, s, s, s);
    }

    sql += ' ORDER BY d.id DESC';
    const donations = await query(sql, params);

    // Summary calculation
    let totalSum = 0;
    let completedSum = 0;
    donations.forEach(d => {
      totalSum += parseFloat(d.amount);
      if (d.payment_status === 'Completed') {
        completedSum += parseFloat(d.amount);
      }
    });

    return res.json({
      success: true,
      data: donations,
      summary: {
        total_records: donations.length,
        total_amount: totalSum,
        completed_amount: completedSum
      }
    });
  } catch (error) {
    console.error('Admin donations error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to retrieve donations.' });
  }
});

router.get('/sponsors', async (req, res) => {
  try {
    const sponsors = await query(`
      SELECT s.*, COUNT(sp.id) as active_sponsorships, COALESCE(SUM(sp.amount), 0) as total_sponsored
      FROM sponsors s
      LEFT JOIN sponsorships sp ON s.id = sp.sponsor_id
      GROUP BY s.id
      ORDER BY s.id DESC
    `);
    return res.json({ success: true, data: sponsors });
  } catch (error) {
    console.error('Admin sponsors error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to retrieve sponsors.' });
  }
});

router.get('/sponsorships', async (req, res) => {
  try {
    const sponsorships = await query(`
      SELECT sp.*, s.name as sponsor_name, s.email as sponsor_email, s.phone as sponsor_phone,
             c.name as cow_name, c.tag_number as cow_tag, c.image as cow_image
      FROM sponsorships sp
      JOIN sponsors s ON sp.sponsor_id = s.id
      JOIN cows c ON sp.cow_id = c.id
      ORDER BY sp.id DESC
    `);
    return res.json({ success: true, data: sponsorships });
  } catch (error) {
    console.error('Admin sponsorships error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to retrieve sponsorships.' });
  }
});

// ----------------------------------------------------
// 6. PRODUCTS & CATEGORIES
// ----------------------------------------------------
router.get('/products', async (req, res) => {
  try {
    const products = await query(`
      SELECT p.*, pc.name as category_name
      FROM products p
      LEFT JOIN product_categories pc ON p.category_id = pc.id
      ORDER BY p.id DESC
    `);
    return res.json({ success: true, data: products });
  } catch (error) {
    console.error('Admin products error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to retrieve products.' });
  }
});

router.post('/products', upload.single('image'), async (req, res) => {
  try {
    const { category_id, name, slug, description, price, stock, image_url, status } = req.body;
    if (!name || !price) {
      return res.status(400).json({ success: false, message: 'Product name and price are required.' });
    }

    const cleanSlug = (slug || name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '')) + '-' + Date.now().toString().slice(-4);
    const finalImage = getUploadedImagePath(req.file, image_url);

    const result = await query(
      `INSERT INTO products (category_id, name, slug, description, price, stock, image, status)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        category_id ? parseInt(category_id, 10) : null,
        name.trim(),
        cleanSlug,
        description || null,
        parseFloat(price),
        parseInt(stock || 0, 10),
        finalImage,
        status || 'active'
      ]
    );

    await logActivity(req.session.adminUser.id, req.session.adminUser.name, 'CREATE_PRODUCT', 'products', result.insertId, `Added product: ${name}`, req);

    return res.json({ success: true, message: 'Product created successfully!', id: result.insertId });
  } catch (error) {
    console.error('Create product error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to create product.' });
  }
});

router.put('/products/:id', upload.single('image'), async (req, res) => {
  try {
    const productId = parseInt(req.params.id, 10);
    const { category_id, name, description, price, stock, image_url, status } = req.body;

    const existing = await query('SELECT * FROM products WHERE id = ?', [productId]);
    if (existing.length === 0) {
      return res.status(404).json({ success: false, message: 'Product not found.' });
    }

    let finalImage = existing[0].image;
    if (req.file) {
      finalImage = `/uploads/${req.file.filename}`;
    } else if (image_url) {
      finalImage = image_url;
    }

    await query(
      `UPDATE products SET category_id = ?, name = ?, description = ?, price = ?, stock = ?, image = ?, status = ? WHERE id = ?`,
      [
        category_id ? parseInt(category_id, 10) : existing[0].category_id,
        name ? name.trim() : existing[0].name,
        description !== undefined ? description : existing[0].description,
        price !== undefined ? parseFloat(price) : existing[0].price,
        stock !== undefined ? parseInt(stock, 10) : existing[0].stock,
        finalImage,
        status || existing[0].status,
        productId
      ]
    );

    return res.json({ success: true, message: 'Product updated successfully!' });
  } catch (error) {
    console.error('Update product error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to update product.' });
  }
});

router.delete('/products/:id', async (req, res) => {
  try {
    const productId = parseInt(req.params.id, 10);
    await query('DELETE FROM products WHERE id = ?', [productId]);
    return res.json({ success: true, message: 'Product deleted successfully.' });
  } catch (error) {
    console.error('Delete product error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to delete product.' });
  }
});

// ----------------------------------------------------
// 7. ORDERS MANAGEMENT
// ----------------------------------------------------
router.get('/orders', async (req, res) => {
  try {
    const orders = await query(`
      SELECT o.*, COUNT(oi.id) as item_count
      FROM orders o
      LEFT JOIN order_items oi ON o.id = oi.order_id
      GROUP BY o.id
      ORDER BY o.id DESC
    `);
    return res.json({ success: true, data: orders });
  } catch (error) {
    console.error('Admin orders error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to retrieve orders.' });
  }
});

router.get('/orders/:id', async (req, res) => {
  try {
    const orderId = parseInt(req.params.id, 10);
    const orders = await query('SELECT * FROM orders WHERE id = ?', [orderId]);
    if (orders.length === 0) {
      return res.status(404).json({ success: false, message: 'Order not found.' });
    }

    const items = await query('SELECT * FROM order_items WHERE order_id = ?', [orderId]);
    return res.json({
      success: true,
      data: {
        ...orders[0],
        items: items
      }
    });
  } catch (error) {
    console.error('Admin order detail error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to retrieve order details.' });
  }
});

router.put('/orders/:id/status', async (req, res) => {
  try {
    const orderId = parseInt(req.params.id, 10);
    const { order_status, payment_status, notes } = req.body;

    await query(
      `UPDATE orders SET
        order_status = COALESCE(?, order_status),
        payment_status = COALESCE(?, payment_status),
        notes = COALESCE(?, notes)
       WHERE id = ?`,
      [order_status || null, payment_status || null, notes || null, orderId]
    );

    return res.json({ success: true, message: 'Order status updated successfully.' });
  } catch (error) {
    console.error('Update order status error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to update order status.' });
  }
});

// ----------------------------------------------------
// 8. BLOGS CRUD
// ----------------------------------------------------
router.get('/blogs', async (req, res) => {
  try {
    const blogs = await query(`
      SELECT b.*, bc.name as category_name
      FROM blogs b
      LEFT JOIN blog_categories bc ON b.category_id = bc.id
      ORDER BY b.id DESC
    `);
    return res.json({ success: true, data: blogs });
  } catch (error) {
    console.error('Admin blogs error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to retrieve blogs.' });
  }
});

router.post('/blogs', upload.single('image'), async (req, res) => {
  try {
    const { category_id, title, slug, excerpt, content, author, status, published_at, image_url } = req.body;
    if (!title || !content) {
      return res.status(400).json({ success: false, message: 'Title and Content are required.' });
    }

    const cleanSlug = (slug || title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '')) + '-' + Date.now().toString().slice(-4);
    const finalImage = getUploadedImagePath(req.file, image_url);
    const pubDate = published_at || new Date().toISOString().split('T')[0];

    const result = await query(
      `INSERT INTO blogs (category_id, title, slug, excerpt, content, featured_image, author, status, published_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        category_id ? parseInt(category_id, 10) : null,
        title.trim(),
        cleanSlug,
        excerpt || null,
        content,
        finalImage,
        author || 'Kamadhenu Seva Team',
        status || 'Published',
        pubDate
      ]
    );

    return res.json({ success: true, message: 'Blog article created successfully!', id: result.insertId });
  } catch (error) {
    console.error('Create blog error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to create blog.' });
  }
});

router.put('/blogs/:id', upload.single('image'), async (req, res) => {
  try {
    const blogId = parseInt(req.params.id, 10);
    const { category_id, title, excerpt, content, author, status, published_at, image_url } = req.body;

    const existing = await query('SELECT * FROM blogs WHERE id = ?', [blogId]);
    if (existing.length === 0) {
      return res.status(404).json({ success: false, message: 'Blog article not found.' });
    }

    let finalImage = existing[0].featured_image;
    if (req.file) {
      finalImage = `/uploads/${req.file.filename}`;
    } else if (image_url) {
      finalImage = image_url;
    }

    await query(
      `UPDATE blogs SET category_id = ?, title = ?, excerpt = ?, content = ?, featured_image = ?, author = ?, status = ?, published_at = ? WHERE id = ?`,
      [
        category_id ? parseInt(category_id, 10) : existing[0].category_id,
        title ? title.trim() : existing[0].title,
        excerpt !== undefined ? excerpt : existing[0].excerpt,
        content !== undefined ? content : existing[0].content,
        finalImage,
        author || existing[0].author,
        status || existing[0].status,
        published_at || existing[0].published_at,
        blogId
      ]
    );

    return res.json({ success: true, message: 'Blog article updated successfully!' });
  } catch (error) {
    console.error('Update blog error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to update blog.' });
  }
});

router.delete('/blogs/:id', async (req, res) => {
  try {
    const blogId = parseInt(req.params.id, 10);
    await query('DELETE FROM blogs WHERE id = ?', [blogId]);
    return res.json({ success: true, message: 'Blog article deleted successfully.' });
  } catch (error) {
    console.error('Delete blog error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to delete blog.' });
  }
});

// ----------------------------------------------------
// 9. EVENTS CRUD
// ----------------------------------------------------
router.get('/events', async (req, res) => {
  try {
    const events = await query('SELECT * FROM events ORDER BY event_date DESC, id DESC');
    return res.json({ success: true, data: events });
  } catch (error) {
    console.error('Admin events error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to retrieve events.' });
  }
});

router.post('/events', upload.single('image'), async (req, res) => {
  try {
    const { title, slug, description, event_date, start_time, end_time, location, registration_url, image_url, status } = req.body;
    if (!title || !event_date || !location) {
      return res.status(400).json({ success: false, message: 'Title, event date, and location are required.' });
    }

    const cleanSlug = (slug || title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '')) + '-' + Date.now().toString().slice(-4);
    const finalImage = getUploadedImagePath(req.file, image_url);

    const result = await query(
      `INSERT INTO events (title, slug, description, event_date, start_time, end_time, location, registration_url, image, status)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        title.trim(),
        cleanSlug,
        description || null,
        event_date,
        start_time || null,
        end_time || null,
        location.trim(),
        registration_url || null,
        finalImage,
        status || 'Upcoming'
      ]
    );

    return res.json({ success: true, message: 'Event created successfully!', id: result.insertId });
  } catch (error) {
    console.error('Create event error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to create event.' });
  }
});

router.put('/events/:id', upload.single('image'), async (req, res) => {
  try {
    const eventId = parseInt(req.params.id, 10);
    const { title, description, event_date, start_time, end_time, location, registration_url, image_url, status } = req.body;

    const existing = await query('SELECT * FROM events WHERE id = ?', [eventId]);
    if (existing.length === 0) {
      return res.status(404).json({ success: false, message: 'Event not found.' });
    }

    let finalImage = existing[0].image;
    if (req.file) {
      finalImage = `/uploads/${req.file.filename}`;
    } else if (image_url) {
      finalImage = image_url;
    }

    await query(
      `UPDATE events SET title = ?, description = ?, event_date = ?, start_time = ?, end_time = ?, location = ?, registration_url = ?, image = ?, status = ? WHERE id = ?`,
      [
        title ? title.trim() : existing[0].title,
        description !== undefined ? description : existing[0].description,
        event_date || existing[0].event_date,
        start_time !== undefined ? start_time : existing[0].start_time,
        end_time !== undefined ? end_time : existing[0].end_time,
        location ? location.trim() : existing[0].location,
        registration_url !== undefined ? registration_url : existing[0].registration_url,
        finalImage,
        status || existing[0].status,
        eventId
      ]
    );

    return res.json({ success: true, message: 'Event updated successfully!' });
  } catch (error) {
    console.error('Update event error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to update event.' });
  }
});

router.delete('/events/:id', async (req, res) => {
  try {
    const eventId = parseInt(req.params.id, 10);
    await query('DELETE FROM events WHERE id = ?', [eventId]);
    return res.json({ success: true, message: 'Event deleted successfully.' });
  } catch (error) {
    console.error('Delete event error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to delete event.' });
  }
});

// ----------------------------------------------------
// 10. GALLERY CRUD
// ----------------------------------------------------
router.get('/gallery', async (req, res) => {
  try {
    const gallery = await query(`
      SELECT g.*, gc.name as category_name
      FROM gallery g
      LEFT JOIN gallery_categories gc ON g.category_id = gc.id
      ORDER BY g.display_order ASC, g.id DESC
    `);
    return res.json({ success: true, data: gallery });
  } catch (error) {
    console.error('Admin gallery error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to retrieve gallery items.' });
  }
});

router.post('/gallery', upload.array('images', 10), async (req, res) => {
  try {
    const { category_id, title, image_url, status } = req.body;
    const catId = category_id ? parseInt(category_id, 10) : null;
    const defaultTitle = title || 'Kamadhenu Goushala Photo';

    // If multiple uploaded files
    if (req.files && req.files.length > 0) {
      for (const f of req.files) {
        const url = `/uploads/${f.filename}`;
        await query(
          `INSERT INTO gallery (category_id, title, image_url, status) VALUES (?, ?, ?, ?)`,
          [catId, defaultTitle, url, status || 'active']
        );
      }
      return res.json({ success: true, message: `${req.files.length} photos uploaded successfully!` });
    } else if (image_url) {
      const result = await query(
        `INSERT INTO gallery (category_id, title, image_url, status) VALUES (?, ?, ?, ?)`,
        [catId, defaultTitle, image_url, status || 'active']
      );
      return res.json({ success: true, message: 'Photo added successfully!', id: result.insertId });
    }

    return res.status(400).json({ success: false, message: 'Please provide an image file or URL.' });
  } catch (error) {
    console.error('Create gallery error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to add gallery photo.' });
  }
});

router.delete('/gallery/:id', async (req, res) => {
  try {
    const galleryId = parseInt(req.params.id, 10);
    await query('DELETE FROM gallery WHERE id = ?', [galleryId]);
    return res.json({ success: true, message: 'Gallery item deleted.' });
  } catch (error) {
    console.error('Delete gallery error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to delete gallery item.' });
  }
});

// ----------------------------------------------------
// 11. TESTIMONIALS & TIMELINE CRUD
// ----------------------------------------------------
router.get('/testimonials', async (req, res) => {
  try {
    const rows = await query('SELECT * FROM testimonials ORDER BY display_order ASC, id DESC');
    return res.json({ success: true, data: rows });
  } catch (error) {
    return res.status(500).json({ success: false, message: 'Failed to retrieve testimonials.' });
  }
});

router.post('/testimonials', upload.single('avatar'), async (req, res) => {
  try {
    const { author_name, designation, message, rating, avatar_url, status, display_order } = req.body;
    if (!author_name || !message) {
      return res.status(400).json({ success: false, message: 'Author name and message are required.' });
    }
    const avatar = getUploadedImagePath(req.file, avatar_url);

    const result = await query(
      `INSERT INTO testimonials (author_name, designation, message, rating, avatar, status, display_order)
       VALUES (?, ?, ?, ?, ?, ?, ?)`,
      [author_name.trim(), designation || 'Donor', message.trim(), parseInt(rating || 5, 10), avatar, status || 'active', parseInt(display_order || 0, 10)]
    );
    return res.json({ success: true, message: 'Testimonial added successfully!', id: result.insertId });
  } catch (error) {
    return res.status(500).json({ success: false, message: 'Failed to add testimonial.' });
  }
});

router.put('/testimonials/:id', upload.single('avatar'), async (req, res) => {
  try {
    const id = parseInt(req.params.id, 10);
    const { author_name, designation, message, rating, avatar_url, status, display_order } = req.body;
    const existing = await query('SELECT * FROM testimonials WHERE id = ?', [id]);
    if (existing.length === 0) return res.status(404).json({ success: false, message: 'Testimonial not found.' });

    let avatar = existing[0].avatar;
    if (req.file) avatar = `/uploads/${req.file.filename}`;
    else if (avatar_url) avatar = avatar_url;

    await query(
      `UPDATE testimonials SET author_name = ?, designation = ?, message = ?, rating = ?, avatar = ?, status = ?, display_order = ? WHERE id = ?`,
      [author_name || existing[0].author_name, designation || existing[0].designation, message || existing[0].message, rating || existing[0].rating, avatar, status || existing[0].status, display_order || existing[0].display_order, id]
    );
    return res.json({ success: true, message: 'Testimonial updated successfully.' });
  } catch (error) {
    return res.status(500).json({ success: false, message: 'Failed to update testimonial.' });
  }
});

router.delete('/testimonials/:id', async (req, res) => {
  try {
    await query('DELETE FROM testimonials WHERE id = ?', [parseInt(req.params.id, 10)]);
    return res.json({ success: true, message: 'Testimonial deleted.' });
  } catch (error) {
    return res.status(500).json({ success: false, message: 'Failed to delete testimonial.' });
  }
});

router.get('/timeline', async (req, res) => {
  try {
    const rows = await query('SELECT * FROM timeline ORDER BY display_order ASC, year ASC');
    return res.json({ success: true, data: rows });
  } catch (error) {
    return res.status(500).json({ success: false, message: 'Failed to retrieve timeline.' });
  }
});

router.post('/timeline', async (req, res) => {
  try {
    const { year, title, description, display_order, status } = req.body;
    if (!year || !title || !description) return res.status(400).json({ success: false, message: 'Year, title, and description required.' });
    const result = await query(
      'INSERT INTO timeline (year, title, description, display_order, status) VALUES (?, ?, ?, ?, ?)',
      [year.trim(), title.trim(), description.trim(), parseInt(display_order || 0, 10), status || 'active']
    );
    return res.json({ success: true, message: 'Timeline entry added!', id: result.insertId });
  } catch (error) {
    return res.status(500).json({ success: false, message: 'Failed to add timeline entry.' });
  }
});

router.put('/timeline/:id', async (req, res) => {
  try {
    const id = parseInt(req.params.id, 10);
    const { year, title, description, display_order, status } = req.body;
    await query(
      'UPDATE timeline SET year = ?, title = ?, description = ?, display_order = ?, status = ? WHERE id = ?',
      [year, title, description, parseInt(display_order || 0, 10), status, id]
    );
    return res.json({ success: true, message: 'Timeline entry updated.' });
  } catch (error) {
    return res.status(500).json({ success: false, message: 'Failed to update timeline.' });
  }
});

router.delete('/timeline/:id', async (req, res) => {
  try {
    await query('DELETE FROM timeline WHERE id = ?', [parseInt(req.params.id, 10)]);
    return res.json({ success: true, message: 'Timeline entry deleted.' });
  } catch (error) {
    return res.status(500).json({ success: false, message: 'Failed to delete timeline.' });
  }
});

// ----------------------------------------------------
// 12. MESSAGES & NEWSLETTER
// ----------------------------------------------------
router.get('/messages', async (req, res) => {
  try {
    const rows = await query('SELECT * FROM contact_messages ORDER BY id DESC');
    return res.json({ success: true, data: rows });
  } catch (error) {
    return res.status(500).json({ success: false, message: 'Failed to retrieve messages.' });
  }
});

router.put('/messages/:id', async (req, res) => {
  try {
    const id = parseInt(req.params.id, 10);
    const { status, admin_notes } = req.body;
    await query(
      'UPDATE contact_messages SET status = COALESCE(?, status), admin_notes = COALESCE(?, admin_notes) WHERE id = ?',
      [status || null, admin_notes || null, id]
    );
    return res.json({ success: true, message: 'Message updated successfully.' });
  } catch (error) {
    return res.status(500).json({ success: false, message: 'Failed to update message.' });
  }
});

router.delete('/messages/:id', async (req, res) => {
  try {
    await query('DELETE FROM contact_messages WHERE id = ?', [parseInt(req.params.id, 10)]);
    return res.json({ success: true, message: 'Message deleted.' });
  } catch (error) {
    return res.status(500).json({ success: false, message: 'Failed to delete message.' });
  }
});

router.get('/newsletter', async (req, res) => {
  try {
    const subscribers = await query('SELECT * FROM newsletter_subscribers ORDER BY id DESC');
    return res.json({ success: true, data: subscribers });
  } catch (error) {
    return res.status(500).json({ success: false, message: 'Failed to retrieve newsletter subscribers.' });
  }
});

// ----------------------------------------------------
// 13. SETTINGS & ACTIVITY LOGS
// ----------------------------------------------------
router.get('/settings', async (req, res) => {
  try {
    const rows = await query('SELECT * FROM settings');
    return res.json({ success: true, data: rows });
  } catch (error) {
    return res.status(500).json({ success: false, message: 'Failed to retrieve settings.' });
  }
});

router.put('/settings', upload.single('site_logo_file'), async (req, res) => {
  try {
    const settingsObj = req.body;

    if (req.file) {
      settingsObj.site_logo = `/uploads/${req.file.filename}`;
    }

    for (const [key, val] of Object.entries(settingsObj)) {
      if (typeof val === 'string' || typeof val === 'number') {
        await query(
          `INSERT INTO settings (setting_key, setting_value)
           VALUES (?, ?)
           ON DUPLICATE KEY UPDATE setting_value = ?`,
          [key, String(val), String(val)]
        );
      }
    }

    await logActivity(req.session.adminUser.id, req.session.adminUser.name, 'UPDATE_SETTINGS', 'settings', null, 'Updated website settings', req);

    return res.json({ success: true, message: 'Settings saved successfully!' });
  } catch (error) {
    console.error('Update settings error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to save settings.' });
  }
});

router.get('/logs', async (req, res) => {
  try {
    const logs = await query('SELECT * FROM admin_activity_logs ORDER BY id DESC LIMIT 100');
    return res.json({ success: true, data: logs });
  } catch (error) {
    return res.status(500).json({ success: false, message: 'Failed to retrieve logs.' });
  }
});

module.exports = router;
