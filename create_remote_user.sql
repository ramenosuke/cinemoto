-- Create a new user for remote access
CREATE USER 'remote_user'@'%' IDENTIFIED BY 'your_secure_password';

-- Grant privileges to the user for the plantitos_cinema database
GRANT ALL PRIVILEGES ON plantitos_cinema.* TO 'remote_user'@'%';

-- Apply the changes
FLUSH PRIVILEGES; 