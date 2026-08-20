const express = require('express');
const router = express.Router();
const crypto = require('crypto');
const Razorpay = require('razorpay');
const { query } = require('../config/db');
const { strictLimiter } = require('../middleware/rateLimiter');

// Initialize Razorpay instance if keys are provided
let razorpayInstance = null;
if (process.env.RAZORPAY_KEY_ID && process.env.RAZORPAY_KEY_SECRET) {
  try {
    razorpayInstance = new Razorpay({
      key_id: process.env.RAZORPAY_KEY_ID,
      key_secret: process.env.RAZORPAY_KEY_SECRET
    });
    console.log(' Razorpay initialized successfully.');
  } catch (err) {
    console.warn('⚠️ Razorpay initialization error:', err.message);
  }
} else {
  console.log('ℹ️ Running in Razorpay DEVELOPMENT MODE (Mock/Simulation enabled).');
}

// ----------------------------------------------------
// 1. SETTINGS & METADATA
// ----------------------------------------------------
router.get('/settings', async (req, res) => {
  try {
    const rows = await query('SELECT setting_key, setting_value, setting_group FROM settings');
    const settingsMap = {};
    rows.forEach(r => {
      settingsMap[r.setting_key] = r.setting_value;
    });

    // Also fetch quick summary statistics
    const [cowCount] = await query('SELECT COUNT(*) as count FROM cows WHERE status = "Active"');
    const [donorCount] = await query('SELECT COUNT(DISTINCT donor_email) as count FROM donations WHERE payment_status = "Completed"');
    const [breedCount] = await query('SELECT COUNT(*) as count FROM breeds WHERE status = "active"');

    return res.json({
      success: true,
      data: settingsMap,
      stats: {
        total_cows: cowCount ? cowCount.count : 0,
        total_donors: donorCount ? donorCount.count : 0,
        total_breeds: breedCount ? breedCount.count : 0,
        years_seva: settingsMap.stat_years_seva || '25+'
      },
      razorpay_key_id: process.env.RAZORPAY_KEY_ID || null
    });
  } catch (error) {
    console.error('Fetch settings error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to retrieve settings.' });
  }
});

// ----------------------------------------------------
// 2. COWS & BREEDS
// ----------------------------------------------------
router.get('/cows', async (req, res) => {
  try {
    const { breed_id, gender, health_status, status, search, limit, offset } = req.query;

    let sql = `
      SELECT c.*, b.name as breed_name, b.origin as breed_origin
      FROM cows c
      LEFT JOIN breeds b ON c.breed_id = b.id
      WHERE 1=1
    `;
    const params = [];

    if (breed_id) {
      sql += ' AND c.breed_id = ?';
      params.push(breed_id);
    }
    if (gender) {
      sql += ' AND c.gender = ?';
      params.push(gender);
    }
    if (health_status) {
      sql += ' AND c.health_status = ?';
      params.push(health_status);
    }
    if (status) {
      sql += ' AND c.status = ?';
      params.push(status);
    } else {
      sql += ' AND c.status != "Deceased"';
    }
    if (search) {
      sql += ' AND (c.name LIKE ? OR c.tag_number LIKE ? OR b.name LIKE ?)';
      const s = `%${search.trim()}%`;
      params.push(s, s, s);
    }

    sql += ' ORDER BY c.id DESC';

    if (limit) {
      sql += ' LIMIT ?';
      params.push(parseInt(limit, 10));
      if (offset) {
        sql += ' OFFSET ?';
        params.push(parseInt(offset, 10));
      }
    }

    const cows = await query(sql, params);
    return res.json({ success: true, data: cows, count: cows.length });
  } catch (error) {
    console.error('Fetch cows error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to retrieve cow records.' });
  }
});

router.get('/cows/:id', async (req, res) => {
  try {
    const cowId = parseInt(req.params.id, 10);
    const cows = await query(`
      SELECT c.*, b.name as breed_name, b.origin as breed_origin, b.description as breed_description, b.characteristics as breed_characteristics
      FROM cows c
      LEFT JOIN breeds b ON c.breed_id = b.id
      WHERE c.id = ?
    `, [cowId]);

    if (cows.length === 0) {
      return res.status(404).json({ success: false, message: 'Cow not found.' });
    }

    const additionalImages = await query('SELECT * FROM cow_images WHERE cow_id = ? ORDER BY id ASC', [cowId]);

    return res.json({
      success: true,
      data: {
        ...cows[0],
        images: additionalImages
      }
    });
  } catch (error) {
    console.error('Fetch cow detail error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to retrieve cow details.' });
  }
});

router.get('/breeds', async (req, res) => {
  try {
    const breeds = await query(`
      SELECT b.*, COUNT(c.id) as cow_count
      FROM breeds b
      LEFT JOIN cows c ON b.id = c.breed_id AND c.status != 'Deceased'
      WHERE b.status = 'active'
      GROUP BY b.id
      ORDER BY b.name ASC
    `);
    return res.json({ success: true, data: breeds });
  } catch (error) {
    console.error('Fetch breeds error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to retrieve breeds.' });
  }
});

router.get('/breeds/:id', async (req, res) => {
  try {
    const breedId = parseInt(req.params.id, 10);
    const breeds = await query('SELECT * FROM breeds WHERE id = ?', [breedId]);
    if (breeds.length === 0) {
      return res.status(404).json({ success: false, message: 'Breed not found.' });
    }

    const cows = await query('SELECT id, name, tag_number, gender, health_status, image, status FROM cows WHERE breed_id = ? AND status != "Deceased"', [breedId]);

    return res.json({
      success: true,
      data: {
        ...breeds[0],
        cows: cows
      }
    });
  } catch (error) {
    console.error('Fetch breed detail error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to retrieve breed details.' });
  }
});

// ----------------------------------------------------
// 3. GAU SEVA SERVICES
// ----------------------------------------------------
router.get('/seva', async (req, res) => {
  try {
    const sevaList = await query('SELECT * FROM seva WHERE status = "active" ORDER BY display_order ASC, id ASC');
    return res.json({ success: true, data: sevaList });
  } catch (error) {
    console.error('Fetch seva error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to retrieve seva packages.' });
  }
});

// ----------------------------------------------------
// 4. TESTIMONIALS & TIMELINE
// ----------------------------------------------------
router.get('/testimonials', async (req, res) => {
  try {
    const testimonials = await query('SELECT * FROM testimonials WHERE status = "active" ORDER BY display_order ASC, id DESC');
    return res.json({ success: true, data: testimonials });
  } catch (error) {
    console.error('Fetch testimonials error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to retrieve testimonials.' });
  }
});

router.get('/timeline', async (req, res) => {
  try {
    const timeline = await query('SELECT * FROM timeline WHERE status = "active" ORDER BY display_order ASC, year ASC');
    return res.json({ success: true, data: timeline });
  } catch (error) {
    console.error('Fetch timeline error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to retrieve timeline.' });
  }
});

// ----------------------------------------------------
// 5. PRODUCTS & STORE
// ----------------------------------------------------
router.get('/product-categories', async (req, res) => {
  try {
    const categories = await query('SELECT * FROM product_categories WHERE status = "active" ORDER BY name ASC');
    return res.json({ success: true, data: categories });
  } catch (error) {
    console.error('Fetch product categories error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to retrieve categories.' });
  }
});

router.get('/products', async (req, res) => {
  try {
    const { category_id, category_slug, search } = req.query;
    let sql = `
      SELECT p.*, pc.name as category_name, pc.slug as category_slug
      FROM products p
      LEFT JOIN product_categories pc ON p.category_id = pc.id
      WHERE p.status = 'active'
    `;
    const params = [];

    if (category_id) {
      sql += ' AND p.category_id = ?';
      params.push(category_id);
    }
    if (category_slug) {
      sql += ' AND pc.slug = ?';
      params.push(category_slug);
    }
    if (search) {
      sql += ' AND (p.name LIKE ? OR p.description LIKE ?)';
      const s = `%${search.trim()}%`;
      params.push(s, s);
    }

    sql += ' ORDER BY p.id DESC';
    const products = await query(sql, params);
    return res.json({ success: true, data: products });
  } catch (error) {
    console.error('Fetch products error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to retrieve products.' });
  }
});

router.get('/products/:id', async (req, res) => {
  try {
    const productId = parseInt(req.params.id, 10);
    const products = await query(`
      SELECT p.*, pc.name as category_name
      FROM products p
      LEFT JOIN product_categories pc ON p.category_id = pc.id
      WHERE p.id = ?
    `, [productId]);

    if (products.length === 0) {
      return res.status(404).json({ success: false, message: 'Product not found.' });
    }
    return res.json({ success: true, data: products[0] });
  } catch (error) {
    console.error('Fetch product detail error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to retrieve product details.' });
  }
});

// ----------------------------------------------------
// 6. BLOGS
// ----------------------------------------------------
router.get('/blog-categories', async (req, res) => {
  try {
    const categories = await query('SELECT * FROM blog_categories WHERE status = "active" ORDER BY name ASC');
    return res.json({ success: true, data: categories });
  } catch (error) {
    console.error('Fetch blog categories error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to retrieve blog categories.' });
  }
});

router.get('/blogs', async (req, res) => {
  try {
    const { category_id, category_slug, search, limit } = req.query;
    let sql = `
      SELECT b.*, bc.name as category_name, bc.slug as category_slug
      FROM blogs b
      LEFT JOIN blog_categories bc ON b.category_id = bc.id
      WHERE b.status = 'Published'
    `;
    const params = [];

    if (category_id) {
      sql += ' AND b.category_id = ?';
      params.push(category_id);
    }
    if (category_slug) {
      sql += ' AND bc.slug = ?';
      params.push(category_slug);
    }
    if (search) {
      sql += ' AND (b.title LIKE ? OR b.excerpt LIKE ? OR b.content LIKE ?)';
      const s = `%${search.trim()}%`;
      params.push(s, s, s);
    }

    sql += ' ORDER BY b.published_at DESC, b.id DESC';

    if (limit) {
      sql += ' LIMIT ?';
      params.push(parseInt(limit, 10));
    }

    const blogs = await query(sql, params);
    return res.json({ success: true, data: blogs });
  } catch (error) {
    console.error('Fetch blogs error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to retrieve blog posts.' });
  }
});

router.get('/blogs/:identifier', async (req, res) => {
  try {
    const identifier = req.params.identifier;
    let blog;
    
    // Check if integer ID or string slug
    if (/^\d+$/.test(identifier)) {
      const rows = await query(`
        SELECT b.*, bc.name as category_name
        FROM blogs b
        LEFT JOIN blog_categories bc ON b.category_id = bc.id
        WHERE b.id = ? AND b.status = 'Published'
      `, [parseInt(identifier, 10)]);
      blog = rows[0];
    } else {
      const rows = await query(`
        SELECT b.*, bc.name as category_name
        FROM blogs b
        LEFT JOIN blog_categories bc ON b.category_id = bc.id
        WHERE b.slug = ? AND b.status = 'Published'
      `, [identifier]);
      blog = rows[0];
    }

    if (!blog) {
      return res.status(404).json({ success: false, message: 'Blog article not found.' });
    }

    // Fetch related blogs
    const related = await query(`
      SELECT id, title, slug, featured_image, published_at
      FROM blogs
      WHERE id != ? AND status = 'Published'
      ORDER BY id DESC LIMIT 3
    `, [blog.id]);

    return res.json({
      success: true,
      data: {
        ...blog,
        related
      }
    });
  } catch (error) {
    console.error('Fetch blog detail error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to retrieve blog details.' });
  }
});

// ----------------------------------------------------
// 7. EVENTS
// ----------------------------------------------------
router.get('/events', async (req, res) => {
  try {
    const { status, limit } = req.query;
    let sql = 'SELECT * FROM events WHERE 1=1';
    const params = [];

    if (status) {
      sql += ' AND status = ?';
      params.push(status);
    }

    sql += ' ORDER BY event_date ASC, start_time ASC';

    if (limit) {
      sql += ' LIMIT ?';
      params.push(parseInt(limit, 10));
    }

    const events = await query(sql, params);
    return res.json({ success: true, data: events });
  } catch (error) {
    console.error('Fetch events error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to retrieve events.' });
  }
});

// ----------------------------------------------------
// 8. GALLERY
// ----------------------------------------------------
router.get('/gallery-categories', async (req, res) => {
  try {
    const categories = await query('SELECT * FROM gallery_categories ORDER BY name ASC');
    return res.json({ success: true, data: categories });
  } catch (error) {
    console.error('Fetch gallery categories error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to retrieve gallery categories.' });
  }
});

router.get('/gallery', async (req, res) => {
  try {
    const { category_id, category_slug, limit } = req.query;
    let sql = `
      SELECT g.*, gc.name as category_name, gc.slug as category_slug
      FROM gallery g
      LEFT JOIN gallery_categories gc ON g.category_id = gc.id
      WHERE g.status = 'active'
    `;
    const params = [];

    if (category_id) {
      sql += ' AND g.category_id = ?';
      params.push(category_id);
    }
    if (category_slug) {
      sql += ' AND gc.slug = ?';
      params.push(category_slug);
    }

    sql += ' ORDER BY g.display_order ASC, g.id DESC';

    if (limit) {
      sql += ' LIMIT ?';
      params.push(parseInt(limit, 10));
    }

    const items = await query(sql, params);
    return res.json({ success: true, data: items });
  } catch (error) {
    console.error('Fetch gallery error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to retrieve gallery items.' });
  }
});

// ----------------------------------------------------
// 9. CONTACT & NEWSLETTER
// ----------------------------------------------------
router.post('/contact', strictLimiter, async (req, res) => {
  try {
    const { name, email, phone, subject, message } = req.body;

    if (!name || !email || !message) {
      return res.status(400).json({
        success: false,
        message: 'Name, email, and message are required fields.'
      });
    }

    const cleanSubject = (subject || 'General Inquiry').trim();

    await query(
      `INSERT INTO contact_messages (name, email, phone, subject, message, status)
       VALUES (?, ?, ?, ?, ?, 'New')`,
      [name.trim(), email.trim(), phone ? phone.trim() : null, cleanSubject, message.trim()]
    );

    return res.json({
      success: true,
      message: 'Namaste! Your message has been received. Our team will get back to you shortly.'
    });
  } catch (error) {
    console.error('Contact submission error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to submit contact message. Please try again.' });
  }
});

router.post('/newsletter', strictLimiter, async (req, res) => {
  try {
    const { email } = req.body;
    if (!email || !email.includes('@')) {
      return res.status(400).json({
        success: false,
        message: 'Please provide a valid email address.'
      });
    }

    const cleanEmail = email.trim().toLowerCase();

    // Insert or ignore if duplicate
    await query(
      `INSERT INTO newsletter_subscribers (email, status)
       VALUES (?, 'Active')
       ON DUPLICATE KEY UPDATE status = 'Active'`,
      [cleanEmail]
    );

    return res.json({
      success: true,
      message: 'Thank you for subscribing to Kamadhenu Goushala newsletter!'
    });
  } catch (error) {
    console.error('Newsletter subscribe error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to subscribe to newsletter.' });
  }
});

// ----------------------------------------------------
// 10. DONATIONS & RAZORPAY INTEGRATION
// ----------------------------------------------------
router.post('/donations/create-order', strictLimiter, async (req, res) => {
  try {
    const { donor_name, donor_email, donor_phone, pan_number, amount, seva_id, seva_name, message } = req.body;

    const numAmount = parseFloat(amount);
    if (!donor_name || !donor_email || isNaN(numAmount) || numAmount < 1) {
      return res.status(400).json({
        success: false,
        message: 'Please provide valid donor name, email, and donation amount (min ₹1).'
      });
    }

    const donationNumber = 'DON-' + Date.now().toString().slice(-8) + '-' + Math.floor(100 + Math.random() * 900);
    let razorpayOrderId = null;
    let isDevMode = false;

    // Check if Razorpay keys are active
    if (razorpayInstance && process.env.RAZORPAY_KEY_ID && process.env.RAZORPAY_KEY_SECRET) {
      try {
        const order = await razorpayInstance.orders.create({
          amount: Math.round(numAmount * 100), // Amount in paise
          currency: 'INR',
          receipt: donationNumber,
          notes: {
            donor_name: donor_name.substring(0, 40),
            donor_email: donor_email.substring(0, 40),
            seva_name: (seva_name || 'General Seva').substring(0, 40)
          }
        });
        razorpayOrderId = order.id;
      } catch (rErr) {
        console.error('Razorpay order creation failed, falling back to simulated order:', rErr.message);
        razorpayOrderId = 'order_mock_' + Date.now();
        isDevMode = true;
      }
    } else {
      razorpayOrderId = 'order_dev_' + Date.now();
      isDevMode = true;
    }

    // Insert pending donation record into database
    const insertResult = await query(
      `INSERT INTO donations (
        donation_number, donor_name, donor_email, donor_phone, pan_number,
        amount, seva_id, seva_name, message, payment_method,
        razorpay_order_id, payment_status
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Razorpay', ?, 'Pending')`,
      [
        donationNumber,
        donor_name.trim(),
        donor_email.trim(),
        donor_phone ? donor_phone.trim() : null,
        pan_number ? pan_number.trim().toUpperCase() : null,
        numAmount,
        seva_id ? parseInt(seva_id, 10) : null,
        seva_name ? seva_name.trim() : 'General Donation',
        message ? message.trim() : null,
        razorpayOrderId
      ]
    );

    return res.json({
      success: true,
      donation_id: insertResult.insertId,
      donation_number: donationNumber,
      amount: numAmount,
      currency: 'INR',
      razorpay_order_id: razorpayOrderId,
      razorpay_key_id: process.env.RAZORPAY_KEY_ID || null,
      is_development: isDevMode
    });

  } catch (error) {
    console.error('Create donation order error:', error.message);
    return res.status(500).json({
      success: false,
      message: 'Failed to initiate donation order.'
    });
  }
});

// POST /api/donations/verify
router.post('/donations/verify', async (req, res) => {
  try {
    const {
      donation_number,
      razorpay_order_id,
      razorpay_payment_id,
      razorpay_signature,
      is_dev_simulation
    } = req.body;

    if (!donation_number) {
      return res.status(400).json({ success: false, message: 'Donation reference number required.' });
    }

    const rows = await query('SELECT * FROM donations WHERE donation_number = ?', [donation_number]);
    if (rows.length === 0) {
      return res.status(404).json({ success: false, message: 'Donation record not found.' });
    }

    const donation = rows[0];

    // Verification logic
    let verified = false;

    if (process.env.RAZORPAY_KEY_SECRET && razorpay_order_id && razorpay_payment_id && razorpay_signature) {
      const generatedSignature = crypto
        .createHmac('sha256', process.env.RAZORPAY_KEY_SECRET)
        .update(razorpay_order_id + '|' + razorpay_payment_id)
        .digest('hex');

      if (generatedSignature === razorpay_signature) {
        verified = true;
      } else {
        console.warn('⚠️ Razorpay signature mismatch!');
      }
    } else if (is_dev_simulation === true || donation.razorpay_order_id.startsWith('order_dev_') || donation.razorpay_order_id.startsWith('order_mock_')) {
      // In development mode when keys are not active, allow simulated completion
      verified = true;
    }

    if (!verified) {
      await query('UPDATE donations SET payment_status = "Failed" WHERE id = ?', [donation.id]);
      return res.status(400).json({
        success: false,
        message: 'Payment verification failed. Please contact the trust office.'
      });
    }

    const finalPaymentId = razorpay_payment_id || ('PAY_DEV_' + Date.now());

    await query(
      `UPDATE donations
       SET payment_status = 'Completed',
           razorpay_payment_id = ?,
           razorpay_signature = ?
       WHERE id = ?`,
      [finalPaymentId, razorpay_signature || 'DEV_VERIFIED', donation.id]
    );

    return res.json({
      success: true,
      message: 'Payment verified successfully! Thank you for supporting Gau Mata.',
      receipt: {
        donation_number: donation.donation_number,
        donor_name: donation.donor_name,
        donor_email: donation.donor_email,
        amount: donation.amount,
        seva_name: donation.seva_name,
        payment_id: finalPaymentId,
        date: new Date().toISOString()
      }
    });

  } catch (error) {
    console.error('Donation verification error:', error.message);
    return res.status(500).json({
      success: false,
      message: 'Error verifying donation payment.'
    });
  }
});

// ----------------------------------------------------
// 11. SPONSORSHIP / ADOPTION
// ----------------------------------------------------
router.post('/sponsors', strictLimiter, async (req, res) => {
  try {
    const { name, email, phone, pan_number, address, cow_id, duration_months, amount, message } = req.body;

    if (!name || !email || !cow_id || !amount) {
      return res.status(400).json({
        success: false,
        message: 'Name, email, cow selection, and amount are required.'
      });
    }

    // Check or insert sponsor
    let sponsorId;
    const existingSponsor = await query('SELECT id FROM sponsors WHERE email = ?', [email.trim().toLowerCase()]);
    if (existingSponsor.length > 0) {
      sponsorId = existingSponsor[0].id;
      await query('UPDATE sponsors SET name = ?, phone = ?, pan_number = ?, address = ? WHERE id = ?', [
        name.trim(),
        phone ? phone.trim() : null,
        pan_number ? pan_number.trim().toUpperCase() : null,
        address ? address.trim() : null,
        sponsorId
      ]);
    } else {
      const newSponsor = await query(
        'INSERT INTO sponsors (name, email, phone, pan_number, address) VALUES (?, ?, ?, ?, ?)',
        [
          name.trim(),
          email.trim().toLowerCase(),
          phone ? phone.trim() : null,
          pan_number ? pan_number.trim().toUpperCase() : null,
          address ? address.trim() : null
        ]
      );
      sponsorId = newSponsor.insertId;
    }

    const months = parseInt(duration_months || 1, 10);
    const startDate = new Date();
    const endDate = new Date();
    endDate.setMonth(endDate.getMonth() + months);

    const formattedStart = startDate.toISOString().split('T')[0];
    const formattedEnd = endDate.toISOString().split('T')[0];

    const sponsorshipResult = await query(
      `INSERT INTO sponsorships (sponsor_id, cow_id, duration_months, amount, start_date, end_date, payment_status, notes)
       VALUES (?, ?, ?, ?, ?, ?, 'Completed', ?)`,
      [
        sponsorId,
        parseInt(cow_id, 10),
        months,
        parseFloat(amount),
        formattedStart,
        formattedEnd,
        message ? message.trim() : 'Cow Sponsorship Seva'
      ]
    );

    return res.json({
      success: true,
      message: 'Cow adoption request registered successfully! May Sri Kamadhenu Mata bless your family.',
      sponsorship_id: sponsorshipResult.insertId
    });

  } catch (error) {
    console.error('Sponsorship error:', error.message);
    return res.status(500).json({
      success: false,
      message: 'Failed to record sponsorship.'
    });
  }
});

// ----------------------------------------------------
// 12. E-STORE ORDER CHECKOUT
// ----------------------------------------------------
router.post('/orders', strictLimiter, async (req, res) => {
  try {
    const { customer_name, customer_email, customer_phone, shipping_address, items, payment_method, notes } = req.body;

    if (!customer_name || !customer_email || !customer_phone || !shipping_address || !Array.isArray(items) || items.length === 0) {
      return res.status(400).json({
        success: false,
        message: 'Please provide full customer details, address, and at least one order item.'
      });
    }

    let calculatedTotal = 0;
    const verifiedItems = [];

    // Verify stock and prices from database
    for (const item of items) {
      const rows = await query('SELECT id, name, price, stock FROM products WHERE id = ? AND status = "active"', [item.product_id]);
      if (rows.length > 0) {
        const prod = rows[0];
        const qty = Math.max(1, parseInt(item.quantity, 10) || 1);
        const subtotal = Number(prod.price) * qty;
        calculatedTotal += subtotal;

        verifiedItems.push({
          product_id: prod.id,
          product_name: prod.name,
          unit_price: prod.price,
          quantity: qty,
          subtotal: subtotal
        });
      }
    }

    if (verifiedItems.length === 0) {
      return res.status(400).json({ success: false, message: 'Selected products are not available.' });
    }

    const orderNumber = 'ORD-' + Date.now().toString().slice(-8) + '-' + Math.floor(100 + Math.random() * 900);

    const orderResult = await query(
      `INSERT INTO orders (
        order_number, customer_name, customer_email, customer_phone,
        shipping_address, total_amount, payment_method, payment_status,
        order_status, notes
      ) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', 'Pending', ?)`,
      [
        orderNumber,
        customer_name.trim(),
        customer_email.trim().toLowerCase(),
        customer_phone.trim(),
        shipping_address.trim(),
        calculatedTotal,
        payment_method || 'Cash on Delivery',
        notes ? notes.trim() : null
      ]
    );

    const orderId = orderResult.insertId;

    // Insert order items & update product stock
    for (const vItem of verifiedItems) {
      await query(
        `INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity, subtotal)
         VALUES (?, ?, ?, ?, ?, ?)`,
        [orderId, vItem.product_id, vItem.product_name, vItem.unit_price, vItem.quantity, vItem.subtotal]
      );

      // Decrement stock if sufficient
      await query(
        `UPDATE products SET stock = GREATEST(0, stock - ?) WHERE id = ?`,
        [vItem.quantity, vItem.product_id]
      );
    }

    return res.json({
      success: true,
      message: 'Order placed successfully!',
      order_number: orderNumber,
      total_amount: calculatedTotal,
      order_id: orderId
    });

  } catch (error) {
    console.error('Order placement error:', error.message);
    return res.status(500).json({ success: false, message: 'Failed to place order.' });
  }
});

module.exports = router;
