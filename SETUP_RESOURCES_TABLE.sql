-- Setup Resources Table
-- Run this SQL in your MySQL database

CREATE TABLE IF NOT EXISTS resources (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(100),
    resource_type ENUM('article', 'video', 'pdf', 'breathing', 'meditation', 'helpline') NOT NULL,
    resource_url VARCHAR(500),
    file_path VARCHAR(500),
    audio_url VARCHAR(500),
    contact_info TEXT,
    contact_numbers VARCHAR(255),
    duration VARCHAR(50),
    author VARCHAR(255),
    is_featured BOOLEAN DEFAULT 0,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    INDEX idx_resource_type (resource_type),
    INDEX idx_is_active (is_active),
    INDEX idx_category (category),
    INDEX idx_created_at (created_at),
    FULLTEXT INDEX ft_search (title, description, category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample resources
INSERT INTO resources (title, description, category, resource_type, resource_url, author, duration, is_active, is_featured) VALUES 
('মানসিক স্বাস্থ্য বোঝা', 'মানসিক স্বাস্থ্য কী এবং এটি কেন গুরুত্বপূর্ণ সে সম্পর্কে বিস্তারিত জানুন।', 'মৌলিক', 'article', 'https://example.com/article1', 'ডা. রহিম', NULL, 1, 1),
('ধ্যান এবং উদ্বেগ', 'কীভাবে ধ্যানের মাধ্যমে উদ্বেগ কমাতে হয় তা শিখুন।', 'ধ্যান', 'video', 'https://youtube.com/watch?v=example', 'যোগী সরকার', '12:35', 1, 0),
('সকালের ধ্যান', 'দিনটি শুরু করার জন্য একটি ৫ মিনিটের ধ্যান সেশন।', 'ধ্যান', 'meditation', NULL, 'ধ্যান বিশেষজ্ঞ', '5:00', 1, 0),
('স্ট্রেস ম্যানেজমেন্ট গাইড', 'স্ট্রেস সামলানোর কার্যকর কৌশল এবং নিয়মিত ব্যায়াম।', 'স্ট্রেস', 'pdf', '/uploads/stress-guide.pdf', 'ডা. সারা', NULL, 1, 0),
('জরুরি মানসিক স্বাস্থ্য সহায়তা', 'আপনার মানসিক স্বাস্থ্য সংকটে আমরা এখানে আছি।', 'হেল্পলাইন', 'helpline', NULL, 'মানসিক স্বাস্থ্য বাংলাদেশ', NULL, 1, 1),
('শ্বাস-প্রশ্বাসের ব্যায়াম', '৪-৭-৮ শ্বাস-প্রশ্বাসের পদ্ধতি যা উদ্বেগ কমায়।', 'শ্বাস-প্রশ্বাস', 'breathing', 'https://example.com/breathing', 'বিশেষজ্ঞ প্রশিক্ষক', '10:00', 1, 0);
