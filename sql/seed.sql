-- ============================================================================
-- Calculator Hub - Sample Data (Seed)
-- ============================================================================
--
-- Run this AFTER schema.sql to populate initial data
-- This includes categories, sample calculators, and admin user
--
-- @author  Your Name
-- @version 1.0.0
-- ============================================================================

SET NAMES utf8mb4;


-- ============================================================================
-- INSERT: Categories
-- ============================================================================

INSERT INTO `calculator_categories` (`name`, `slug`, `icon`, `short_desc`, `sort_order`, `is_active`) VALUES
('Finance', 'finance', '💰', 'Loan, EMI, Interest, Investment calculators', 1, 1),
('Health & Fitness', 'health', '❤️', 'BMI, Calories, Body Fat, Pregnancy calculators', 2, 1),
('Math', 'math', '📐', 'Scientific, Percentage, Fraction calculators', 3, 1),
('Date & Time', 'date-time', '📅', 'Age, Date Difference, Countdown calculators', 4, 1),
('Conversion', 'conversion', '🔄', 'Unit, Currency, Temperature converters', 5, 1),
('Everyday', 'everyday', '🏠', 'Tip, Discount, Fuel, Shopping calculators', 6, 1);


-- ============================================================================
-- INSERT: Sample Calculators
-- ============================================================================

-- BMI Calculator (Health)
INSERT INTO `calculators` (
    `category_id`, `name`, `slug`, `short_desc`,
    `meta_title`, `meta_desc`, `h1_title`,
    `primary_keyword`, `secondary_keywords`,
    `intro_html`, `content_html`, `faq_json`,
    `js_file`, `is_active`, `is_indexed`, `show_in_sitemap`
) VALUES (
    2, 'BMI Calculator', 'bmi', 'Calculate your Body Mass Index',
    'BMI Calculator - Calculate Body Mass Index Online Free',
    'Free online BMI calculator. Enter your height and weight to calculate your Body Mass Index instantly. Find out if you are underweight, normal, overweight, or obese.',
    'BMI Calculator - Body Mass Index',
    'bmi calculator', 'body mass index, bmi calc, calculate bmi, bmi checker, weight calculator',
    '<p>Use this <strong>BMI Calculator</strong> to check your Body Mass Index based on your height and weight. BMI is a useful measure of whether you are a healthy weight.</p>',
    '<h2>What is BMI?</h2><p>Body Mass Index (BMI) is a simple calculation using your height and weight. The formula is BMI = kg/m² where kg is your weight in kilograms and m² is your height in metres squared.</p><h2>BMI Categories</h2><ul><li>Underweight: BMI less than 18.5</li><li>Normal weight: BMI 18.5 to 24.9</li><li>Overweight: BMI 25 to 29.9</li><li>Obese: BMI 30 or greater</li></ul>',
    '[{"question": "What is a healthy BMI?", "answer": "A healthy BMI is between 18.5 and 24.9. This indicates a normal weight for your height."}, {"question": "How accurate is BMI?", "answer": "BMI is a useful screening tool but does not directly measure body fat. Athletes may have high BMI due to muscle mass."}]',
    'bmi.js', 1, 1, 1
);

-- Loan EMI Calculator (Finance)
INSERT INTO `calculators` (
    `category_id`, `name`, `slug`, `short_desc`,
    `meta_title`, `meta_desc`, `h1_title`,
    `primary_keyword`, `secondary_keywords`,
    `intro_html`, `js_file`, `is_active`, `is_indexed`, `show_in_sitemap`
) VALUES (
    1, 'Loan EMI Calculator', 'loan-emi', 'Calculate monthly EMI for loans',
    'Loan EMI Calculator - Calculate Monthly EMI Online',
    'Free EMI calculator for home loan, car loan, personal loan. Calculate monthly EMI, total interest, and payment schedule instantly.',
    'Loan EMI Calculator',
    'emi calculator', 'loan calculator, home loan emi, car loan calculator, personal loan emi, monthly installment',
    '<p>Calculate your <strong>Equated Monthly Installment (EMI)</strong> for any loan. Enter loan amount, interest rate, and tenure to get instant results.</p>',
    'loan-emi.js', 1, 1, 1
);

-- Age Calculator (Date & Time)
INSERT INTO `calculators` (
    `category_id`, `name`, `slug`, `short_desc`,
    `meta_title`, `meta_desc`, `h1_title`,
    `primary_keyword`, `secondary_keywords`,
    `intro_html`, `js_file`, `is_active`, `is_indexed`, `show_in_sitemap`
) VALUES (
    4, 'Age Calculator', 'age', 'Calculate exact age in years, months, days',
    'Age Calculator - Calculate Exact Age from Date of Birth',
    'Free online age calculator. Calculate your exact age in years, months, weeks, days, hours, and minutes from your date of birth.',
    'Age Calculator - How Old Am I?',
    'age calculator', 'calculate age, date of birth calculator, how old am i, age from dob, birthday calculator',
    '<p>Find out your <strong>exact age</strong> in years, months, days, and even hours! Just enter your date of birth below.</p>',
    'age.js', 1, 1, 1
);

-- Percentage Calculator (Math)
INSERT INTO `calculators` (
    `category_id`, `name`, `slug`, `short_desc`,
    `meta_title`, `meta_desc`, `h1_title`,
    `primary_keyword`, `secondary_keywords`,
    `intro_html`, `js_file`, `is_active`, `is_indexed`, `show_in_sitemap`
) VALUES (
    3, 'Percentage Calculator', 'percentage', 'Calculate percentages easily',
    'Percentage Calculator - Calculate Percent Online Free',
    'Free percentage calculator. Calculate X% of Y, percentage increase/decrease, and find what percent X is of Y. Quick and easy!',
    'Percentage Calculator',
    'percentage calculator', 'percent calculator, calculate percentage, percentage increase, percentage decrease, find percentage',
    '<p>Calculate <strong>percentages</strong> quickly with multiple calculation modes. Find X% of Y, calculate percentage change, and more.</p>',
    'percentage.js', 1, 1, 1
);

-- GST Calculator (Finance)
INSERT INTO `calculators` (
    `category_id`, `name`, `slug`, `short_desc`,
    `meta_title`, `meta_desc`, `h1_title`,
    `primary_keyword`, `secondary_keywords`,
    `intro_html`, `js_file`, `is_active`, `is_indexed`, `show_in_sitemap`
) VALUES (
    1, 'GST Calculator', 'gst', 'Calculate GST amount and total',
    'GST Calculator India - Calculate GST Amount Online',
    'Free GST calculator for India. Calculate GST at 5%, 12%, 18%, 28% rates. Find GST amount, net price, and gross price instantly.',
    'GST Calculator India',
    'gst calculator', 'gst calculator india, calculate gst, gst amount, cgst sgst calculator, igst calculator',
    '<p>Calculate <strong>GST (Goods and Services Tax)</strong> for any amount. Supports all GST slabs: 5%, 12%, 18%, and 28%.</p>',
    'gst.js', 1, 1, 1
);

-- Calorie Calculator (Health)
INSERT INTO `calculators` (
    `category_id`, `name`, `slug`, `short_desc`,
    `meta_title`, `meta_desc`, `h1_title`,
    `primary_keyword`, `secondary_keywords`,
    `intro_html`, `js_file`, `is_active`, `is_indexed`, `show_in_sitemap`
) VALUES (
    2, 'Calorie Calculator', 'calorie', 'Calculate daily calorie needs',
    'Calorie Calculator - Daily Calorie Needs Calculator',
    'Free calorie calculator. Calculate how many calories you need per day based on age, gender, weight, height, and activity level.',
    'Daily Calorie Calculator',
    'calorie calculator', 'daily calorie calculator, calorie needs, tdee calculator, bmr calculator, calories per day',
    '<p>Calculate your <strong>daily calorie needs</strong> based on your body metrics and activity level. Perfect for weight loss or muscle gain goals.</p>',
    'calorie.js', 1, 1, 1
);


-- ============================================================================
-- INSERT: Admin User
-- ============================================================================
-- Password: admin123 (change this immediately after first login!)
-- Hash generated with: password_hash('admin123', PASSWORD_DEFAULT)

INSERT INTO `users` (`name`, `email`, `password_hash`, `role`, `is_active`) VALUES
('Admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1);


-- ============================================================================
-- INSERT: Site Settings
-- ============================================================================

INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_group`, `description`) VALUES
('site_name', 'CalcHub', 'general', 'Website name'),
('site_tagline', 'All-in-One Calculator Hub', 'general', 'Site tagline/slogan'),
('site_url', 'https://yourdomain.com', 'general', 'Full site URL with https'),
('admin_email', 'admin@example.com', 'general', 'Admin contact email'),

('adsense_client', '', 'ads', 'Google AdSense publisher ID (ca-pub-XXXX)'),
('adsense_slot_header', '', 'ads', 'Ad slot ID for header'),
('adsense_slot_sidebar', '', 'ads', 'Ad slot ID for sidebar'),
('adsense_slot_content', '', 'ads', 'Ad slot ID for in-content'),
('adsense_slot_footer', '', 'ads', 'Ad slot ID for footer'),

('ga_tracking_id', '', 'analytics', 'Google Analytics tracking ID (G-XXXX)'),
('enable_custom_analytics', '1', 'analytics', 'Enable custom analytics tracking'),

('meta_default_title', '%s - CalcHub', 'seo', 'Default title format (%s = page title)'),
('meta_default_desc', 'Free online %s calculator. Fast, accurate, and mobile-friendly.', 'seo', 'Default meta description'),

('maintenance_mode', '0', 'general', 'Enable maintenance mode (0=off, 1=on)');


-- ============================================================================
-- END OF SEED DATA
-- ============================================================================

-- Verify inserted data:
-- SELECT * FROM calculator_categories;
-- SELECT id, name, slug, category_id FROM calculators;
-- SELECT id, name, email, role FROM users;
-- SELECT * FROM settings;
