const readline = require('readline');
const bcrypt = require('bcryptjs');
const { query, checkConnection } = require('../config/db');

async function createAdmin() {
  const args = process.argv.slice(2);
  let name = '';
  let email = '';
  let password = '';
  let role = 'superadmin';

  if (args.length >= 3) {
    name = args[0];
    email = args[1];
    password = args[2];
    if (args[3]) role = args[3];
    await saveAdmin(name, email, password, role);
    process.exit(0);
  }

  const rl = readline.createInterface({
    input: process.stdin,
    output: process.stdout
  });

  const question = (q) => new Promise((resolve) => rl.question(q, resolve));

  console.log('\n=============================================');
  console.log(' Create New Administrator - Kamadhenu Goushala');
  console.log('=============================================\n');

  try {
    name = await question('Admin Full Name: ');
    while (!name.trim()) {
      name = await question('Name cannot be empty. Full Name: ');
    }

    email = await question('Admin Email Address: ');
    while (!email.trim() || !email.includes('@')) {
      email = await question('Please enter a valid email address: ');
    }

    password = await question('Admin Password (min 6 chars): ');
    while (!password || password.length < 6) {
      password = await question('Password must be at least 6 characters: ');
    }

    rl.close();

    await saveAdmin(name.trim(), email.trim().toLowerCase(), password, role);
  } catch (err) {
    console.error('Error:', err.message);
    rl.close();
  }
}

async function saveAdmin(name, email, password, role) {
  try {
    const isConnected = await checkConnection();
    if (!isConnected) {
      console.error('❌ Could not connect to MySQL database.');
      process.exit(1);
    }

    // Check if email already exists
    const existing = await query('SELECT id FROM admins WHERE email = ?', [email]);
    const salt = await bcrypt.genSalt(10);
    const hash = await bcrypt.hash(password, salt);

    if (existing.length > 0) {
      await query(
        'UPDATE admins SET name = ?, password_hash = ?, role = ?, status = "active" WHERE email = ?',
        [name, hash, role, email]
      );
      console.log(`\n Successfully updated existing admin account: ${email}`);
    } else {
      await query(
        'INSERT INTO admins (name, email, password_hash, role, status) VALUES (?, ?, ?, ?, "active")',
        [name, email, hash, role]
      );
      console.log(`\n Successfully created new admin account: ${email}`);
    }

    console.log('You can now log in at http://localhost:3000/admin\n');
  } catch (error) {
    console.error('❌ Database error:', error.message);
  }
}

if (require.main === module) {
  createAdmin();
}

module.exports = { saveAdmin };
