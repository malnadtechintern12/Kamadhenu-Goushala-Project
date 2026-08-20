const mysql = require('mysql2/promise');
require('dotenv').config();

const dbConfig = {
  host: process.env.DB_HOST || 'localhost',
  port: parseInt(process.env.DB_PORT || '3306', 10),
  user: process.env.DB_USER || 'root',
  password: process.env.DB_PASSWORD || '',
  database: process.env.DB_NAME || 'kamadhenu_goushala',
  waitForConnections: true,
  connectionLimit: 15,
  queueLimit: 0,
  enableKeepAlive: true,
  keepAliveInitialDelay: 0,
  charset: 'utf8mb4'
};

let pool = null;

function getPool() {
  if (!pool) {
    pool = mysql.createPool(dbConfig);
  }
  return pool;
}

// Helper wrapper for parameterized queries
async function query(sql, params = []) {
  try {
    const p = getPool();
    const [rows, fields] = await p.execute(sql, params);
    return rows;
  } catch (error) {
    console.error('Database query error:', error.message);
    throw error;
  }
}

// Test connection and auto-create DB/tables if needed
async function checkConnection() {
  try {
    // First try connecting to the server
    const testConn = await mysql.createConnection({
      host: dbConfig.host,
      port: dbConfig.port,
      user: dbConfig.user,
      password: dbConfig.password
    });
    
    await testConn.query(`CREATE DATABASE IF NOT EXISTS \`${dbConfig.database}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`);
    await testConn.end();
    
    const p = getPool();
    const [result] = await p.query('SELECT 1 + 1 AS solution');
    console.log(` MySQL Connected successfully to database: ${dbConfig.database}`);
    return true;
  } catch (error) {
    console.error('⚠️ MySQL Connection Warning:', error.message);
    console.error('Please ensure XAMPP MySQL or MySQL Server is running on port ' + dbConfig.port);
    return false;
  }
}

module.exports = {
  getPool,
  query,
  checkConnection
};
