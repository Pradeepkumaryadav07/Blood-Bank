# BloodBank - Core PHP (Complete)

This is a complete **Core PHP** Blood Bank web application intended to run on XAMPP/LAMP.

## Setup
1. Copy the folder `BloodBank_full` to your XAMPP `htdocs` folder (e.g. `C:\xampp\htdocs\BloodBank`).
2. Import the SQL file `bloodbank.sql` into MySQL (via phpMyAdmin or CLI) to create the database and tables.
3. Edit `config.php` and set your MySQL username/password if different from `root`/``.
4. Start Apache & MySQL in XAMPP and open: `http://localhost/BloodBank_full/available_samples.php` or `login.php`.
5. Register a hospital and receiver using the registration pages.

## Notes
- Passwords are hashed using `password_hash`.
- Prepared statements (PDO) are used to prevent SQL injection.
- Basic blood compatibility logic is implemented server-side.
- This project is meant for learning and should be hardened (CSRF tokens, input sanitization, HTTPS) for production.

