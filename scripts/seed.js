const fs = require('fs');
const path = require('path');
const mysql = require('mysql2/promise');
const bcrypt = require('bcryptjs');
require('dotenv').config();

async function runSeed() {
  console.log(' Starting Kamadhenu Goushala database setup & seed...');
  
  const host = process.env.DB_HOST || 'localhost';
  const port = parseInt(process.env.DB_PORT || '3306', 10);
  const user = process.env.DB_USER || 'root';
  const password = process.env.DB_PASSWORD || '';
  const database = process.env.DB_NAME || 'kamadhenu_goushala';

  let connection;
  try {
    connection = await mysql.createConnection({
      host,
      port,
      user,
      password,
      multipleStatements: true
    });

    console.log(` Connected to MySQL server at ${host}:${port}`);

    // Create database if not exists
    await connection.query(`CREATE DATABASE IF NOT EXISTS \`${database}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`);
    await connection.query(`USE \`${database}\`;`);
    console.log(` Using database: ${database}`);

    // Read and run schema.sql
    const schemaPath = path.join(__dirname, '..', 'database', 'schema.sql');
    if (fs.existsSync(schemaPath)) {
      console.log(' Applying schema.sql...');
      const schemaSql = fs.readFileSync(schemaPath, 'utf8');
      await connection.query(schemaSql);
      console.log(' Database tables created successfully.');
    }

    // Read and run seed.sql
    const seedPath = path.join(__dirname, '..', 'database', 'seed.sql');
    if (fs.existsSync(seedPath)) {
      console.log(' Applying seed.sql with realistic demo data...');
      const seedSql = fs.readFileSync(seedPath, 'utf8');
      await connection.query(seedSql);
      console.log(' Seed data inserted successfully.');
    }

    // Ensure default admin with properly hashed bcrypt password
    const adminEmail = 'admin@kamadhenugoushala.org';
    const adminPassword = 'Admin@123';
    const salt = await bcrypt.genSalt(10);
    const hash = await bcrypt.hash(adminPassword, salt);

    await connection.query(
      'UPDATE `admins` SET `password_hash` = ? WHERE `email` = ?',
      [hash, adminEmail]
    );

    console.log('\n==================================================');
    console.log(' KAMADHENU GOUSHALA DATABASE INITIALIZED!');
    console.log('==================================================');
    console.log(`Admin Portal: http://localhost:${process.env.PORT || 3000}/admin`);
    console.log(`Admin Email:    ${adminEmail}`);
    console.log(`Admin Password: ${adminPassword}`);
    console.log('==================================================\n');

  } catch (error) {
    console.error('❌ Error during database seeding:', error.message);
    process.exit(1);
  } finally {
    if (connection) {
      await connection.end();
    }
  }
}

if (require.main === module) {
  runSeed();
}

module.exports = runSeed;
