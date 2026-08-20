const express = require('express');
const session = require('express-session');
const cors = require('cors');
const helmet = require('helmet');
const path = require('path');
const fs = require('fs');
require('dotenv').config();

const { checkConnection } = require('./config/db');
const { apiLimiter } = require('./middleware/rateLimiter');
const { router: authRoutes } = require('./routes/auth');
const publicRoutes = require('./routes/public');
const adminRoutes = require('./routes/admin');

const app = express();
const PORT = process.env.PORT || 3000;

// Ensure public upload directories exist
const uploadDir = path.join(__dirname, 'public', 'uploads');
if (!fs.existsSync(uploadDir)) {
  fs.mkdirSync(uploadDir, { recursive: true });
}

// 1. Security & Headers Middleware
app.use(helmet({
  contentSecurityPolicy: false, // Disabled for flexible loading of Google Fonts, CDNs, and images
  crossOriginEmbedderPolicy: false
}));

app.use(cors({
  origin: true,
  credentials: true
}));

// 2. Request Parsing Middleware
app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ extended: true, limit: '10mb' }));

// 3. Express Session Configuration
app.use(session({
  secret: process.env.SESSION_SECRET || 'kamadhenu_secret_session_key_2026_!#$',
  resave: false,
  saveUninitialized: false,
  cookie: {
    httpOnly: true,
    secure: process.env.NODE_ENV === 'production',
    sameSite: 'lax',
    maxAge: 7 * 24 * 60 * 60 * 1000 // 7 days
  }
}));

// 4. Rate Limiting for Public APIs
app.use('/api', apiLimiter);

// 5. Static Files Serving
app.use(express.static(path.join(__dirname, 'public')));
app.use('/uploads', express.static(uploadDir));

// 6. Mount REST API Routes
app.use('/api/auth', authRoutes);
app.use('/api/admin', adminRoutes);
app.use('/api', publicRoutes);

// 7. Route Shortcuts
app.get('/admin', (req, res) => {
  res.sendFile(path.join(__dirname, 'public', 'admin', 'index.html'));
});

// 8. 404 Fallback for unmatched API routes
app.all('/api/*', (req, res) => {
  res.status(404).json({
    success: false,
    message: 'API route not found.'
  });
});

// 9. Global Error Handling Middleware
app.use((err, req, res, next) => {
  console.error('Unhandled Application Error:', err.message);
  
  if (err.name === 'MulterError') {
    return res.status(400).json({
      success: false,
      message: `File upload error: ${err.message}`
    });
  }

  return res.status(500).json({
    success: false,
    message: process.env.NODE_ENV === 'production' 
      ? 'An unexpected server error occurred.' 
      : err.message
  });
});

// 10. Start Server and check database
app.listen(PORT, async () => {
  console.log('\n======================================================');
  console.log(` 🐄 KAMADHENU GOUSHALA SERVER RUNNING`);
  console.log(` 🌐 Website: http://localhost:${PORT}`);
  console.log(` 🛡️ Admin:   http://localhost:${PORT}/admin`);
  console.log(` 📅 Environment: ${process.env.NODE_ENV || 'development'}`);
  console.log('======================================================\n');
  
  await checkConnection();
});

module.exports = app;
