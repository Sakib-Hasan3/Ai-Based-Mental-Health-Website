-- Setup Achievements Tables for Mentora
-- Run this SQL in your MySQL database

-- Main achievements table
CREATE TABLE IF NOT EXISTS achievements_master (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(100),
    badge_icon VARCHAR(100),
    badge_color VARCHAR(7),
    points INT DEFAULT 10,
    requirement_type ENUM('mood_count', 'journal_count', 'streak_days', 'assessment_count', 'community_posts', 'session_count', 'resource_views') NOT NULL,
    requirement_value INT NOT NULL,
    is_featured BOOLEAN DEFAULT 0,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_requirement_type (requirement_type),
    INDEX idx_is_active (is_active),
    FULLTEXT INDEX ft_search (name, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User achievements table (tracks user progress)
CREATE TABLE IF NOT EXISTS user_achievements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    achievement_id INT NOT NULL,
    progress_current INT DEFAULT 0,
    progress_target INT,
    is_completed BOOLEAN DEFAULT 0,
    is_claimed BOOLEAN DEFAULT 0,
    completed_at TIMESTAMP NULL,
    claimed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (achievement_id) REFERENCES achievements_master(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_achievement (user_id, achievement_id),
    INDEX idx_user_id (user_id),
    INDEX idx_is_completed (is_completed),
    INDEX idx_is_claimed (is_claimed)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample achievements
INSERT INTO achievements_master (name, description, badge_icon, badge_color, points, requirement_type, requirement_value, is_active) VALUES 
('প্রথম মুড এন্ট্রি', '১টি মুড এন্ট্রি তৈরি করুন', 'fa-smile', '#FFD700', 5, 'mood_count', 1, 1),
('মুড ট্র্যাকার', '১০টি মুড এন্ট্রি তৈরি করুন', 'fa-grin-stars', '#FFD700', 15, 'mood_count', 10, 1),
('মুড মাস্টার', '৩০টি মুড এন্ট্রি তৈরি করুন', 'fa-grin-hearts', '#FFD700', 50, 'mood_count', 30, 1),
('প্রথম জার্নাল', '১টি জার্নাল এন্ট্রি তৈরি করুন', 'fa-pen', '#87CEEB', 5, 'journal_count', 1, 1),
('জার্নাল লেখক', '১০টি জার্নাল এন্ট্রি তৈরি করুন', 'fa-book', '#87CEEB', 20, 'journal_count', 10, 1),
('জার্নাল মাস্টার', '৫০টি জার্নাল এন্ট্রি তৈরি করুন', 'fa-book-open', '#87CEEB', 100, 'journal_count', 50, 1),
('সপ্তাহের স্ট্রিক', '৭ দিনের স্ট্রিক তৈরি করুন', 'fa-fire', '#FF6347', 25, 'streak_days', 7, 1),
('মাসের স্ট্রিক', '৩০ দিনের স্ট্রিক তৈরি করুন', 'fa-crown', '#FF6347', 100, 'streak_days', 30, 1),
('প্রথম অ্যাসেসমেন্ট', '১টি মানসিক স্বাস্থ্য যাচাই সম্পূর্ণ করুন', 'fa-brain', '#9370DB', 10, 'assessment_count', 1, 1),
('অ্যাসেসমেন্ট বিশেষজ্ঞ', '৫টি মানসিক স্বাস্থ্য যাচাই সম্পূর্ণ করুন', 'fa-flask', '#9370DB', 40, 'assessment_count', 5, 1),
('সামাজিক প্রজাপতি', '১টি কমিউনিটি পোস্ট তৈরি করুন', 'fa-users', '#FF1493', 10, 'community_posts', 1, 1),
('সামাজিক নেতা', '১০টি কমিউনিটি পোস্ট তৈরি করুন', 'fa-handshake', '#FF1493', 50, 'community_posts', 10, 1),
('মেন্টর শিক্ষার্থী', '১টি মেন্টর সেশন সম্পূর্ণ করুন', 'fa-graduation-cap', '#228B22', 15, 'session_count', 1, 1),
('মেন্টর অনুসরণকারী', '৫টি মেন্টর সেশন সম্পূর্ণ করুন', 'fa-university', '#228B22', 60, 'session_count', 5, 1);
