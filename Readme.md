# 🧠 Mentora - Mental Wellness Platform

![Mentora Logo](assets/images/logo.png)

**Mentora** is a comprehensive AI-powered Mental Wellness, Mentorship, and Medical Consultation Platform specifically designed for the Bangladeshi population. It addresses the growing mental health crisis while providing career guidance and medical support through an integrated ecosystem.

---

## 📋 Table of Contents
- [Features](#-features)
- [Technology Stack](#-technology-stack)
- [Project Structure](#-project-structure)
- [Installation & Setup](#-installation--setup)
- [Database Configuration](#-database-configuration)
- [API Documentation](#-api-documentation)
- [Usage Guide](#-usage-guide)
- [Key Features Explained](#-key-features-explained)
- [Support & Contribution](#-support--contribution)

---

## 🌟 Features

### 🤖 AI Mental Health Support
- **24/7 Mood Tracking** - Daily mood logging with trend analysis
- **AI-Powered Predictions** - Machine learning model for mental health assessment
- **Crisis Detection** - Identify high-risk situations
- **Bengali Language Support** - Full UI and chatbot support in Bengali
- **Personal Reports** - Weekly and monthly mental health analytics

### 👨‍🏫 Mentorship System
- **Verified Mentors** - Career, academic, and life guidance
- **Smart Enrollment** - Easy mentor discovery and connection
- **Status Management** - Track active, pending, and completed enrollments
- **Structured Sessions** - One-on-one mentoring with progress tracking
- **Experience Levels** - Different mentor categories

### 🏥 Medical Consultation
- **Doctor Directory** - Browse doctors by specialty and division
- **Appointment Management** - View and manage doctor appointments
- **Rating System** - Rate and review medical consultations
- **Trust & Verification** - Verified healthcare professionals

### 📚 Learning & Development
- **Mental Health Resources** - Articles, videos, PDFs, and audio guides
- **Resource Categories**:
  - Mental health articles
  - Meditation and breathing exercises
  - Video tutorials
  - Helpline contacts
  - PDF guides
- **Content Filtering** - Browse by type and category
- **Detailed Information** - Full resource details and descriptions

### 👥 Community Features
- **Support Forums** - Create and share posts
- **Peer Support Groups** - Connect with others
- **Comments & Reactions** - Engage with community posts
- **Report System** - Flag inappropriate content
- **Activity Feed** - Real-time updates

### 🎮 Gamification & Achievements
- **Achievement System** - Unlock badges and milestones
- **Categories**:
  - First mood entry achievements
  - Mood tracking streaks
  - Journal writing milestones
  - Community engagement badges
  - Assessment completion rewards
  - Mentor enrollment bonuses
- **Progress Tracking** - Visual representation of progress
- **Claim Rewards** - Unlock and claim achievements

### 📱 User Management
- **Secure Registration** - Email verification system
- **Profile Management** - Edit profile and upload avatar
- **Password Security** - Password change and recovery
- **Account Settings** - Privacy and notification preferences
- **Data Export** - Download personal data
- **Account Deletion** - Secure account removal

---

## 🚀 Technology Stack

### Frontend
- **HTML5, CSS3, JavaScript** - Core web technologies
- **Bootstrap 5** - Responsive UI framework
- **jQuery** - DOM manipulation and AJAX
- **Chart.js** - Data visualization and charts
- **Font Awesome 6** - Icon library
- **Google Fonts (Noto Sans Bengali)** - Bengali language support

### Backend
- **PHP 7.4+** - Server-side logic
- **MySQL 5.7+** - Database management
- **Apache Server** - Web server with mod_rewrite

### Machine Learning & AI
- **Python 3.8+** - ML implementation
- **Flask** - API framework
- **scikit-learn** - Machine learning models
- **pandas & NumPy** - Data processing

### Additional Libraries & Tools
- **PHPMailer** - Email sending with verification
- **DomPDF** - PDF generation for reports
- **SSLCommerz** - Payment gateway integration
- **Twilio** - SMS notifications (optional)

---

## 📁 Project Structure

```
mental health/
├── index.php                          # Homepage
├── db.php                             # Database connection
├── 
├── 🗂️ auth/                           # Authentication System
│   ├── register.php                   # Registration page
│   ├── register-process.php           # Registration processing
│   ├── login.php                      # Login page
│   ├── login-process.php              # Login processing
│   ├── logout.php                     # Logout handler
│   ├── forgot-password.php            # Password recovery
│   ├── verify-email.php               # Email verification page
│   ├── verify-email-sent.php          # Verification confirmation
│   ├── test-register.php              # Testing system setup
│   └── REGISTRATION_SETUP.md          # Setup documentation
│
├── 🗂️ api/                            # API Endpoints
│   ├── Core APIs
│   │   ├── get-journals.php           # Fetch user journals
│   │   ├── get-journal-details.php    # Journal details
│   │   ├── save-journal.php           # Create/update journal
│   │   ├── update-journal.php         # Update journal entry
│   │   ├── delete-journal.php         # Delete journal
│   │   ├── save-mood.php              # Log mood entry
│   │   ├── get-mood-history.php       # Mood analytics
│   │   ├── get-mentors.php            # Get mentor list
│   │   ├── get-mentor-details.php     # Mentor information
│   │   ├── enroll-mentor.php          # Enroll in mentoring
│   │   ├── get-my-enrollments.php     # User's mentor enrollments
│   │   ├── update-enrollment-status.php # Manage enrollment
│   │   ├── get-doctors-by-division.php # Search doctors
│   │   ├── submit-rating.php          # Rate service
│   │   └── change-password.php        # Password change
│   │
│   ├── 📁 achievements/               # Achievement System
│   │   ├── get-achievements.php       # Fetch achievements
│   │   └── claim-achievement.php      # Claim achievement
│   │
│   ├── 📁 community/                  # Community Features
│   │   ├── create-post.php            # Create post
│   │   ├── get-posts.php              # Get posts feed
│   │   ├── comment-post.php           # Add comment
│   │   ├── react-post.php             # Like/react post
│   │   ├── get-comments.php           # Get post comments
│   │   ├── delete-post.php            # Delete post
│   │   └── report-post.php            # Report inappropriate content
│   │
│   ├── 📁 resource/                   # Learning Resources
│   │   ├── get-resources.php          # Get resources list
│   │   └── get-resource-details.php   # Resource details
│   │
│   ├── 📁 settings/                   # User Settings
│   │   ├── get-settings.php           # Get user settings
│   │   ├── update-profile.php         # Update profile info
│   │   ├── update-notifications.php   # Notification preferences
│   │   ├── update-privacy.php         # Privacy settings
│   │   ├── export-data.php            # Export user data
│   │   └── delete-account.php         # Delete account
│   │
│   ├── 🐍 ML & Predictions
│   │   ├── mental_health_api.py       # Flask ML API
│   │   ├── predict_model.py           # ML model logic
│   │   ├── predict.php                # PHP wrapper for predictions
│   │   └── __pycache__/               # Python cache
│   │
│   ├── upload-avatar.php              # Avatar upload handler
│   └── predict.php                    # Prediction endpoint
│
├── 🗂️ dashboard/                      # User Dashboard Pages
│   ├── index.php                      # Dashboard home
│   ├── profile.php                    # User profile
│   ├── journal.php                    # Journal management
│   ├── mood-tracker.php               # Mood tracking
│   ├── mentor.php                     # Mentor directory
│   ├── mentor-details.php             # Mentor information
│   ├── my-mentor-enrollments.php      # User's enrollments
│   ├── doctor.php                     # Doctor directory
│   ├── community.php                  # Community forum
│   ├── resources.php                  # Learning resources
│   ├── achievements.php               # Achievements & badges
│   ├── assessment.php                 # Mental health assessment
│   ├── save_assessment.php            # Assessment processing
│   └── settings.php                   # Account settings
│
├── 🗂️ includes/                       # Shared Components
│   ├── auth_check.php                 # Login verification
│   ├── api_auth_check.php             # API authentication
│   ├── db_connection.php              # Database connector
│   ├── functions.php                  # Helper functions
│   ├── journal_helper.php             # Journal utilities
│   └── mentor_helper.php              # Mentor utilities
│
├── 🗂️ config/                         # Configuration
│   ├── constants.php                  # App constants
│   ├── database.php                   # DB config
│   └── email-verification-schema.sql  # DB schema
│
├── 🗂️ assets/                         # Frontend Resources
│   ├── 📁 css/                        # Stylesheets
│   │   ├── style.css                  # Main styles
│   │   ├── login.css                  # Auth pages
│   │   ├── dashboard.css              # Dashboard styles
│   │   ├── journal.css                # Journal styling
│   │   ├── mood-tracker.css           # Mood page styles
│   │   ├── mentor.css                 # Mentor pages
│   │   ├── doctor.css                 # Doctor pages
│   │   ├── community.css              # Community styles
│   │   ├── resources.css              # Resources page
│   │   ├── achievements.css           # Achievements page
│   │   ├── assessment.css             # Assessment styles
│   │   ├── profile.css                # Profile page
│   │   ├── settings.css               # Settings page
│   │   └── bootstrap.min.css          # Bootstrap framework
│   │
│   ├── 📁 js/                         # JavaScript Files
│   │   ├── main.js                    # Main functionality
│   │   ├── login.js                   # Auth scripting
│   │   ├── journal.js                 # Journal features
│   │   ├── mood-tracker.js            # Mood tracking
│   │   ├── mentor.js                  # Mentor system
│   │   ├── doctor.js                  # Doctor features
│   │   ├── community.js               # Community features
│   │   ├── resources.js               # Resources browsing
│   │   ├── achievements.js            # Achievements system
│   │   ├── assessment.js              # Assessment features
│   │   └── bootstrap.bundle.min.js    # Bootstrap JS
│   │
│   ├── 📁 images/                     # Image Assets
│   │   └── avatars/                   # User profile pictures
│   │
│   └── 📁 ml_model/                   # ML Model Files
│       ├── mental_health_model_optimized.pkl
│       ├── encoders.pkl
│       └── model_metadata.json
│
├── 🗂️ venv/                           # Python Virtual Environment
│
├── Readme.md                          # Project documentation
├── INSTALLATION.md                    # Installation guide
├── EMAIL_VERIFICATION_SETUP.md        # Email setup guide
├── sitemap.xml                        # SEO sitemap
├── robots.txt                         # Search engine rules
└── .gitignore                         # Git ignore rules
```

---

## ⚙️ Installation & Setup

### Prerequisites
- **Xampp/Wamp/Lamp** installed with PHP 7.4+ and MySQL 5.7+
- **Python 3.8+** (for ML features)
- **Composer** (optional, for package management)
- **Git** (for version control)

### Step 1: Clone or Download Project
```bash
# Clone from GitHub
git clone https://github.com/Sakib-Hasan3/Ai-Based-Mental-Health-Website.git

# Or extract zip to htdocs folder
# C:\xampp\htdocs\mental health
```

### Step 2: Configure Database Connection
Edit `db.php` in the root directory:
```php
$servername = "localhost";
$username = "root";
$password = "";           // Your MySQL password
$dbname = "mentora_db";  // Your database name
```

### Step 3: Create Database
```bash
# Option 1: Using phpMyAdmin
1. Open http://localhost/phpmyadmin
2. Create new database: "mentora_db"
3. Select character set: utf8mb4

# Option 2: Using MySQL CLI
mysql -u root -p -e "CREATE DATABASE mentora_db CHARACTER SET utf8mb4;"
```

### Step 4: Run Database Schema
```bash
# Import email verification schema
mysql -u root -p mentora_db < config/email-verification-schema.sql

# Import achievements table (if exists)
mysql -u root -p mentora_db < SETUP_ACHIEVEMENTS_TABLE.sql

# Import resources table (if exists)
mysql -u root -p mentora_db < SETUP_RESOURCES_TABLE.sql
```

### Step 5: Configure Python ML Environment
```bash
# Create virtual environment
cd api
python -m venv venv

# Activate virtual environment
# Windows
venv\Scripts\activate

# Linux/Mac
source venv/bin/activate

# Install dependencies
pip install flask flask-cors scikit-learn pandas joblib numpy

# Start Flask API (runs on http://localhost:5000)
python mental_health_api.py
```

### Step 6: Test Installation
Visit in browser:
- **Homepage**: http://localhost/mental%20health/
- **Registration Test**: http://localhost/mental%20health/auth/test-register.php
- **Login**: http://localhost/mental%20health/auth/login.php

---

## 🗄️ Database Configuration

### Database Name
```
mentora_db
```

### Main Tables

#### 1. **users**
```sql
CREATE TABLE users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  full_name VARCHAR(100),
  email VARCHAR(100) UNIQUE,
  phone VARCHAR(15) UNIQUE,
  password VARCHAR(255),
  date_of_birth DATE,
  gender VARCHAR(20),
  address TEXT,
  city VARCHAR(50),
  profile_picture VARCHAR(255),
  is_verified TINYINT(1) DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  login_attempts INT DEFAULT 0,
  last_login_at DATETIME,
  last_login_ip VARCHAR(45),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### 2. **email_verifications**
```sql
CREATE TABLE email_verifications (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT UNIQUE,
  token VARCHAR(64) UNIQUE,
  expires_at DATETIME,
  verified_at DATETIME,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);
```

#### 3. **journals**
```
- user_id, title, content
- category, mood_tag
- is_private, created_at, updated_at
```

#### 4. **mood_entries**
```
- user_id, mood_level (1-5)
- mood_description, created_at
```

#### 5. **mentors**
```
- user_id, expertise, experience_level
- bio, availability, approval_status
```

#### 6. **mentor_enrollments**
```
- user_id, mentor_id, status
- enrolled_date, last_contacted_at
```

#### 7. **achievements_master**
- 15 predefined achievements with points

#### 8. **user_achievements**
- Tracks user progress and claimed achievements

#### 9. **community_posts**
- User posts, comments, and reactions

#### 10. **resources**
- Mental health articles, videos, PDFs, helplines

### Email Verification Setup
See [EMAIL_VERIFICATION_SETUP.md](EMAIL_VERIFICATION_SETUP.md) for detailed configuration

---

## 🔌 API Documentation

### Authentication
All API endpoints require user to be logged in (session-based).

### Core Endpoints

#### Mood Tracking
```php
// Save mood entry
POST /api/save-mood.php
Parameters: mood_level, mood_description

// Get mood history
GET /api/get-mood-history.php
Returns: Array of mood entries with dates
```

#### Journal Management
```php
// Get all journals
GET /api/get-journals.php

// Get journal details
GET /api/get-journal-details.php?id=JOURNAL_ID

// Save/Create journal
POST /api/save-journal.php
Parameters: title, content, category, mood_tag, is_private

// Update journal
POST /api/update-journal.php
Parameters: id, title, content, category, mood_tag, is_private

// Delete journal
POST /api/delete-journal.php
Parameters: id
```

#### Mentor System
```php
// Get all mentors
GET /api/get-mentors.php

// Get mentor details
GET /api/get-mentor-details.php?id=MENTOR_ID

// Enroll with mentor
POST /api/enroll-mentor.php
Parameters: mentor_id

// Get user's enrollments
GET /api/get-my-enrollments.php

// Update enrollment status
POST /api/update-enrollment-status.php
Parameters: enrollment_id, status
```

#### Doctor Directory
```php
// Get doctors by division
GET /api/get-doctors-by-division.php?division=CITY_NAME

// Submit rating
POST /api/submit-rating.php
Parameters: doctor_id, rating, comment
```

#### Community
```php
// Create post
POST /api/community/create-post.php
Parameters: title, content, type

// Get posts feed
GET /api/community/get-posts.php?page=1

// Add comment
POST /api/community/comment-post.php
Parameters: post_id, content

// React to post
POST /api/community/react-post.php
Parameters: post_id, reaction_type
```

#### Achievements
```php
// Get achievements
GET /api/achievements/get-achievements.php

// Claim achievement
POST /api/achievements/claim-achievement.php
Parameters: achievement_id
```

#### Resources
```php
// Get resources
GET /api/resource/get-resources.php?type=article&page=1

// Get resource details
GET /api/resource/get-resource-details.php?id=RESOURCE_ID
```

#### User Settings
```php
// Update profile
POST /api/update-profile.php
Parameters: full_name, phone, address, city, date_of_birth

// Change password
POST /api/change-password.php
Parameters: current_password, new_password

// Update notification preferences
POST /api/settings/update-notifications.php
Parameters: email_notifications, push_notifications

// Export user data
GET /api/settings/export-data.php

// Delete account
POST /api/settings/delete-account.php
Parameters: password
```

#### ML Predictions
```php
// Get mental health prediction
POST /api/predict.php
Parameters: gender, occupation, family_history, etc.
Returns: {
  "prediction": "Low|Moderate|High",
  "confidence": 0.95,
  "recommendation": "string"
}
```

---

## 📖 Usage Guide

### 1. **For New Users**
- Visit homepage
- Click "নতুন অ্যাকাউন্ট তৈরি করুন" (Create Account)
- Fill registration form
- Verify email from inbox
- Complete profile setup

### 2. **Dashboard Navigation**
After login, access main features from dashboard sidebar:
- **মানসিক স্বাস্থ্য** - Mood tracking & AI predictions
- **জার্নাল** - Personal journaling
- **মেন্টর** - Find & enroll with mentors
- **ডাক্তার** - Browse doctors
- **কমিউনিটি** - Community forums
- **সংস্থান** - Learning resources
- **অর্জন** - Track achievements
- **সেটিংস** - Account settings

### 3. **Mood Tracking**
- Daily mood entry (1-5 scale)
- Track patterns & trends
- Get AI-powered insights
- Receive recommendations

### 4. **Journal Writing**
- Create private journals
- Tag with emotions
- Add categories
- Track progress
- Review past entries

### 5. **Mentorship**
- Browse verified mentors
- View expertise & experience
- Enroll in mentoring
- Manage enrollment status
- Track progress

### 6. **Community Engagement**
- Create support posts
- Comment on posts
- React with emojis/likes
- Report inappropriate content
- Build support network

### 7. **Earn Achievements**
- Complete daily tasks
- Unlock badges
- Track streaks
- Claim rewards
- View leaderboards

---

## 🔑 Key Features Explained

### AI Mental Health Model
- **Input Features**: Gender, occupation, family history, stress level, mood swings, etc.
- **Model Type**: scikit-learn classification
- **Output**: Low/Moderate/High mental health risk
- **API**: Flask API running on port 5000
- **Integration**: PHP wrapper calls Flask API

### Email Verification System
- Sends verification email on registration
- 24-hour token expiration
- One-click confirmation
- Prevents unverified logins

### Achievement System
- **15 Achievements** covering multiple categories
- **Progress Tracking** with visual indicators
- **Point System** for gamification
- **Auto-unlock** based on user actions
- **Manual Claim** option

### Resource Management
- **4 Resource Types**: Articles, Videos, PDFs, Helplines
- **Breathing Exercises** for anxiety management
- **Meditation Guides** for stress relief
- **Categorized Content** for easy browsing
- **Detailed Descriptions** with links

---

## 🛠️ Development & Troubleshooting

### Common Issues

#### 1. "সংযোগ ব্যর্থ" (Connection Failed)
- Check `db.php` database credentials
- Verify MySQL is running
- Confirm database exists

#### 2. Email Not Sending
- Check PHP mail configuration
- Configure SMTP if in Windows
- PHPMailer setup required for reliability

#### 3. 404 Errors
- Ensure Apache mod_rewrite is enabled
- Check .htaccess files
- Verify file paths match folder structure

#### 4. Python ML Not Working
- Ensure Flask API is running
- Check port 5000 availability
- Install required Python packages

#### 5. Assets Not Loading
- Check asset paths (use `/mental%20health/`)
- Verify CSS/JS file locations
- Clear browser cache

### Testing

#### Registration System Test
```
http://localhost/mental%20health/auth/test-register.php
```

#### Database Connection Test
```php
// In any PHP file:
include 'includes/db_connection.php';
echo "Connected!";
```

#### Python ML API Test
```bash
# Terminal
curl -X POST http://localhost:5000/api/predict \
  -H "Content-Type: application/json" \
  -d '{"gender":"male","stress_level":"high"}'
```

### Debugging
- **Browser Console** (F12) - Client-side errors
- **PHP Error Logs** - Check Apache logs
- **Network Tab** - API request/response
- **SQL Logs** - Database query issues

---

## 📝 File Descriptions

### Key Configuration Files

#### `db.php`
Main database connection for all PHP files. Edit credentials here.

#### `config/email-verification-schema.sql`
Database tables for email verification. Run once during setup.

#### `includes/functions.php`
Helper functions used throughout the project:
- `sendVerificationEmail()` - Send verification emails
- `generateEmailToken()` - Create secure tokens
- `verifyEmailToken()` - Validate tokens

#### `includes/auth_check.php`
Session validation for protected pages.

### Key PHP Pages

#### `auth/register.php`
User registration interface with form validation.

#### `auth/login.php`
User authentication page.

#### `dashboard/index.php`
Main dashboard after user logs in.

#### `dashboard/profile.php`
User profile management and avatar upload.

---

## 🤝 Support & Contribution

### Getting Help
1. Check relevant documentation (MD files in root)
2. Run test files to diagnose issues
3. Check browser console for errors
4. Review database structure

### Reporting Issues
- Describe the problem clearly
- Include error messages
- Specify steps to reproduce
- Mention browser/PHP version

### Contributing
1. Fork the repository
2. Create feature branch
3. Make changes with clear commits
4. Test thoroughly
5. Submit pull request

---

## 📄 License & Credits

**Project**: Mentora - AI-Based Mental Health Website  
**Repository**: [Sakib-Hasan3/Ai-Based-Mental-Health-Website](https://github.com/Sakib-Hasan3/Ai-Based-Mental-Health-Website)  
**Current Branch**: main  

### Technologies Used
- **Backend**: PHP, MySQL
- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5
- **ML/AI**: Python, Flask, scikit-learn
- **Libraries**: jQuery, Chart.js, PHPMailer, DomPDF

### Language Support
- 🇧🇩 Bengali (Bangla) - Primary
- 🇺🇸 English - Secondary

---

## 📞 Quick Links

| Link | Purpose |
|------|---------|
| [Homepage](http://localhost/mental%20health/) | Main page |
| [Register](http://localhost/mental%20health/auth/register.php) | Create account |
| [Login](http://localhost/mental%20health/auth/login.php) | Sign in |
| [Test System](http://localhost/mental%20health/auth/test-register.php) | Verify setup |
| [phpMyAdmin](http://localhost/phpmyadmin) | Database management |

---

## Last Updated
**March 2026** - Comprehensive documentation for Mentora v1.0

**Happy Coding! 🚀**
