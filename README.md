# DeskFlow

A modern, feature-rich workspace management system built with PHP and MySQL. DeskFlow provides a complete solution for managing workspace resources, bookings, and payments with an intuitive 3D visualization interface.

![DeskFlow Logo](logo.png)

## Features

### User Features
- 🎨 Modern, responsive interface with 3D workspace visualization
- 🔐 Secure user authentication and authorization
- 📅 Easy desk and room booking system
- 💳 Integrated payment processing
- 📱 Mobile-friendly design
- 📊 Real-time availability checking
- 🔔 Notification system for bookings and announcements

### Admin Features
- 👥 Comprehensive user management
- 📋 Resource management (desks and rooms)
- 💰 Payment tracking and management
- 📈 Detailed reporting and analytics
- 📢 Announcement system
- ⚙️ System configuration and settings

## Tech Stack

- **Backend**: PHP 8.2
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript
- **3D Visualization**: Three.js
- **Payment Integration**: Razorpay
- **Server**: XAMPP/Apache

## Installation

1. **Prerequisites**
   - PHP 8.2 or higher
   - MySQL/MariaDB
   - Apache/Nginx web server
   - Composer (for dependencies)

2. **Setup**
   ```bash
   # Clone the repository
   git clone https://github.com/AyushAgrawal2004/DeskFlow.git

   # Navigate to project directory
   cd DeskFlow

   # Import database
   mysql -u root -p < project/coworking2.sql

   # Configure database connection
   # Edit project/dbconnect.php with your database credentials
   ```

3. **Configuration**
   - Update database credentials in `project/dbconnect.php`
   - Configure payment gateway settings
   - Set up email notifications
   - Configure system settings through admin panel

## Database Structure

The system uses the following main tables:
- `users` - User accounts and authentication
- `desks` - Workspace desk information
- `rooms` - Meeting room information
- `bookings` - Reservation records
- `payment_attempts` - Payment transaction records
- `announcements` - System announcements
- `settings` - System configuration
- `user_logs` - User activity tracking

## Usage

1. **User Registration**
   - Register a new account
   - Verify email (if enabled)
   - Complete profile information

2. **Booking Process**
   - Browse available workspaces
   - Select dates and times
   - Choose payment method
   - Confirm booking

3. **Admin Panel**
   - Manage users and permissions
   - Configure workspace resources
   - Monitor bookings and payments
   - Generate reports
   - Send announcements

## Security Features

- Password hashing
- SQL injection prevention
- XSS protection
- CSRF protection
- Session management
- Input validation
- Activity logging

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

For support, create an issue in the repository or contact the maintainer.

## Acknowledgments

- Three.js for 3D visualization
- Razorpay for payment processing
- Bootstrap for frontend framework
- Font Awesome for icons

## Project Status

🚀 Active Development

## Screenshots

![DeskFlow Screenshot](Screenshot%202025-04-18%20200753.png)

## Roadmap

- [ ] Mobile app development
- [ ] Advanced analytics dashboard
- [ ] Integration with calendar systems
- [ ] Multi-language support
- [ ] API development for third-party integration

---

Made with ❤️ by [Ayush Agrawal]
