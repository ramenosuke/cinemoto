-- Create a new user for remote access
CREATE USER 'remote_user'@'%' IDENTIFIED BY 'your_secure_password';

-- Grant privileges to the user for the cinemoto database
GRANT ALL PRIVILEGES ON cinemoto.* TO 'remote_user'@'%';

-- Apply the changes
FLUSH PRIVILEGES; 