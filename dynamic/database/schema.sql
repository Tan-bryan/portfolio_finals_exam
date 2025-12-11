-- Portfolio Database Schema
-- Run this SQL file to create all necessary tables

CREATE DATABASE IF NOT EXISTS portfolio_db;
USE portfolio_db;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Hero section
CREATE TABLE hero_section (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    profile_image VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_hero (user_id)
);

-- About items
CREATE TABLE about_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    icon VARCHAR(50) NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    display_order INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Skills
CREATE TABLE skills (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    category VARCHAR(50) NOT NULL, -- Programming Languages, Frameworks, Tools, Databases, Cloud
    skill_name VARCHAR(100) NOT NULL,
    display_order INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Projects
CREATE TABLE projects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    image VARCHAR(255),
    technologies TEXT, -- Comma-separated list
    project_url VARCHAR(255),
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Education
CREATE TABLE education (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    degree VARCHAR(100) NOT NULL,
    institution VARCHAR(100) NOT NULL,
    start_year INT,
    end_year INT,
    description TEXT,
    address TEXT,
    display_order INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Certifications
CREATE TABLE certifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    issuer VARCHAR(100) NOT NULL,
    issue_date DATE,
    description TEXT,
    display_order INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Contact information
CREATE TABLE contact_info (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    location VARCHAR(100),
    linkedin_url VARCHAR(255),
    github_url VARCHAR(255),
    twitter_url VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_contact (user_id)
);

-- Research projects
CREATE TABLE research_projects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    year INT,
    display_order INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create indexes for better performance
CREATE INDEX idx_user_id ON hero_section(user_id);
CREATE INDEX idx_user_about ON about_items(user_id);
CREATE INDEX idx_user_skills ON skills(user_id);
CREATE INDEX idx_user_projects ON projects(user_id);
CREATE INDEX idx_user_education ON education(user_id);
CREATE INDEX idx_user_certs ON certifications(user_id);
CREATE INDEX idx_user_contact ON contact_info(user_id);
CREATE INDEX idx_user_research ON research_projects(user_id);