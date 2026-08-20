-- ==========================================================
-- Kamadhenu Goushala Demo Seed Data
-- ==========================================================

USE `kamadhenu_goushala`;

-- Disable Foreign Key checks for clean insertion
SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE `admin_activity_logs`;
TRUNCATE TABLE `order_items`;
TRUNCATE TABLE `orders`;
TRUNCATE TABLE `products`;
TRUNCATE TABLE `product_categories`;
TRUNCATE TABLE `cow_images`;
TRUNCATE TABLE `sponsorships`;
TRUNCATE TABLE `sponsors`;
TRUNCATE TABLE `donations`;
TRUNCATE TABLE `cows`;
TRUNCATE TABLE `breeds`;
TRUNCATE TABLE `seva`;
TRUNCATE TABLE `testimonials`;
TRUNCATE TABLE `timeline`;
TRUNCATE TABLE `blogs`;
TRUNCATE TABLE `blog_categories`;
TRUNCATE TABLE `events`;
TRUNCATE TABLE `gallery`;
TRUNCATE TABLE `gallery_categories`;
TRUNCATE TABLE `contact_messages`;
TRUNCATE TABLE `newsletter_subscribers`;
TRUNCATE TABLE `settings`;
TRUNCATE TABLE `admins`;

SET FOREIGN_KEY_CHECKS = 1;

-- 1. Default Admin (Password: Admin@123)
-- bcrypt hash for Admin@123: $2a$10$Zepm3qCvhT38N08P54JtXe5f2yM3W1/5K4e2/n4aY8o1t0Q3Yg69u
INSERT INTO `admins` (`id`, `name`, `email`, `password_hash`, `role`, `status`) VALUES
(1, 'Goushala Administrator', 'admin@kamadhenugoushala.org', '$2a$10$wNlmU9y4gGgY0wZtFpM61OI.8pWqY7wU.6Q7LpU6m0N/z7H1g7R9a', 'superadmin', 'active');

-- 2. Indigenous Cow Breeds
INSERT INTO `breeds` (`id`, `name`, `origin`, `description`, `milk_yield`, `characteristics`, `image`, `status`) VALUES
(1, 'Gir', 'Saurashtra, Gujarat', 'Gir is renowned worldwide for its high tolerance to stress conditions, superior milk yield, and docile nature. Known for its distinct rounded forehead and long pendulous ears.', '12 - 18 Liters / day (A2 Milk)', 'Convex forehead, long hanging ears, reddish-brown to white speckled coat.', 'https://images.unsplash.com/photo-1546445317-29f4545e9d53?auto=format&fit=crop&w=800&q=80', 'active'),
(2, 'Hallikar', 'Karnataka (Mysuru & Mandya)', 'Hallikar is one of the premier drought breeds of India, prized for its strength, endurance, and noble carriage. It originated from the Vijayanagara empire herds.', '4 - 7 Liters / day (Rich A2 Milk)', 'Long tapering horns, compact body, alert disposition, grey-white coat.', 'https://images.unsplash.com/photo-1570042225831-d98fa7577f1e?auto=format&fit=crop&w=800&q=80', 'active'),
(3, 'Malenadu Gidda', 'Western Ghats, Karnataka', 'A sacred dwarf breed native to the rainforests of Western Ghats. Highly disease resistant, thrives on natural forest grazing, yielding medicinal grade A2 milk.', '3 - 5 Liters / day (High Medicinal Value)', 'Dwarf stature, black/brown coat, agile, resilient to heavy monsoon.', 'https://images.unsplash.com/photo-1596733430284-f7437764b1a9?auto=format&fit=crop&w=800&q=80', 'active'),
(4, 'Amrit Mahal', 'Karnataka (Tumakuru & Chikkamagaluru)', 'Historically nurtured by royalty for immense endurance, swiftness, and strength. Known as the cavalry breed of Mysore.', '3 - 6 Liters / day', 'Muzzle-pointed head, magnificent long sharp horns, grey-black shade.', 'https://images.unsplash.com/photo-1527153857715-3908f2ae5e81?auto=format&fit=crop&w=800&q=80', 'active'),
(5, 'Tharparkar', 'Thar Desert, Rajasthan', 'Known as White Sindhi, an extraordinary dual-purpose desert breed capable of thriving in extreme temperatures with rich, sweet A2 milk.', '8 - 14 Liters / day', 'Medium-sized, light grey to pure white coat, lyre-shaped horns.', 'https://images.unsplash.com/photo-1500595046743-cd271d694d30?auto=format&fit=crop&w=800&q=80', 'active'),
(6, 'Ongole', 'Prakasam, Andhra Pradesh', 'Famous majestic Zebu cattle breed recognized for muscular build, majestic royal hump, and worldwide contribution to tropical beef and dairy.', '6 - 10 Liters / day', 'Large majestic body, prominent hump, white glossy coat, deep dewlap.', 'https://images.unsplash.com/photo-1516467508483-a7212febe31a?auto=format&fit=crop&w=800&q=80', 'active'),
(7, 'Kangayam', 'Kangeyam, Tamil Nadu', 'Ancient sturdy drought breed referenced in Sangam literature. Renowned for its vitality, compact body, and drought resilience.', '3 - 6 Liters / day', 'Grey with dark shading on neck and quarters, short stout horns.', 'https://images.unsplash.com/photo-1597848212624-a19eb35e2651?auto=format&fit=crop&w=800&q=80', 'active');

-- 3. Cows
INSERT INTO `cows` (`id`, `tag_number`, `name`, `breed_id`, `gender`, `dob`, `arrival_date`, `health_status`, `story`, `image`, `status`) VALUES
(1, 'KG-GIR-001', 'Nandini', 1, 'Female', '2019-04-12', '2020-02-15', 'Healthy', 'Nandini was rescued from a distressed cattle shed and brought to Kamadhenu Goushala. She is extremely affectionate, loves fresh green grass and jaggery, and is the gentle matriarch of the Gir herd.', 'https://images.unsplash.com/photo-1546445317-29f4545e9d53?auto=format&fit=crop&w=800&q=80', 'Active'),
(2, 'KG-HAL-002', 'Balarama', 2, 'Male', '2018-08-20', '2019-05-10', 'Healthy', 'A majestic Hallikar bull known for his calm dignity and strength. Balarama participates joyfully in our annual Gopashtami and Gau Puja celebrations.', 'https://images.unsplash.com/photo-1570042225831-d98fa7577f1e?auto=format&fit=crop&w=800&q=80', 'Active'),
(3, 'KG-MG-003', 'Gauri', 3, 'Female', '2021-01-15', '2021-06-22', 'Healthy', 'Gauri is an adorable Malenadu Gidda cow who loves roaming in the herbal garden. Visitors frequently sponsor her special mineral supplements and neem feeds.', 'https://images.unsplash.com/photo-1596733430284-f7437764b1a9?auto=format&fit=crop&w=800&q=80', 'Active'),
(4, 'KG-AM-004', 'Kapila', 4, 'Female', '2017-11-05', '2019-01-14', 'Healthy', 'Kapila is a graceful Amrit Mahal cow with shining silver coat and majestic horns. She has birthed two healthy calves at our shelter.', 'https://images.unsplash.com/photo-1527153857715-3908f2ae5e81?auto=format&fit=crop&w=800&q=80', 'Active'),
(5, 'KG-TP-005', 'Surabhi', 5, 'Female', '2020-07-19', '2021-03-30', 'Healthy', 'Surabhi was rescued during a harsh summer drought. Today she is glowing with radiant health, yielding sweet nourishing milk utilized exclusively for temple abhishekams.', 'https://images.unsplash.com/photo-1500595046743-cd271d694d30?auto=format&fit=crop&w=800&q=80', 'Active'),
(6, 'KG-ONG-006', 'Shambhu', 6, 'Male', '2019-02-10', '2020-08-11', 'Healthy', 'Shambhu is a towering Ongole bull with a magnificent hump and serene disposition. He enjoys back scratches and morning sunbaths.', 'https://images.unsplash.com/photo-1516467508483-a7212febe31a?auto=format&fit=crop&w=800&q=80', 'Active'),
(7, 'KG-KAN-007', 'Kavery', 7, 'Female', '2021-09-01', '2022-04-18', 'Healthy', 'Kavery is a joyful Kangayam calf who loves playing with other calves in the open meadow. She is active and fond of carrots and bananas.', 'https://images.unsplash.com/photo-1597848212624-a19eb35e2651?auto=format&fit=crop&w=800&q=80', 'Active'),
(8, 'KG-GIR-008', 'Ganga', 1, 'Female', '2016-03-10', '2018-12-05', 'Elderly Care', 'Ganga is an elderly mother cow receiving geriatric care, soft steamed fodder, daily oil massages, and gentle love in our dedicated senior sanctuary.', 'https://images.unsplash.com/photo-1546445317-29f4545e9d53?auto=format&fit=crop&w=800&q=80', 'Active'),
(9, 'KG-HAL-009', 'Nandi', 2, 'Male', '2022-05-14', '2022-08-01', 'Recovering', 'Nandi arrived with a fractured hind leg which was treated with ayurvedic bone-setting and modern orthopedic support. He is walking smoothly now.', 'https://images.unsplash.com/photo-1570042225831-d98fa7577f1e?auto=format&fit=crop&w=800&q=80', 'Active'),
(10, 'KG-MG-010', 'Mangala', 3, 'Female', '2020-10-18', '2021-11-20', 'Healthy', 'Mangala is a peaceful Malenadu Gidda cow who enjoys Vedic chanting during morning feeding hours.', 'https://images.unsplash.com/photo-1596733430284-f7437764b1a9?auto=format&fit=crop&w=800&q=80', 'Adopted');

-- 4. Seva Services
INSERT INTO `seva` (`id`, `title`, `slug`, `short_desc`, `full_desc`, `suggested_amount`, `icon`, `image`, `display_order`, `status`) VALUES
(1, 'Feed a Cow for a Day', 'feed-a-cow', 'Provide fresh green fodder, dry hay, mineral mixture, and clean drinking water.', 'Your seva ensures wholesome nutrition including seasonal green grass, protein-rich cattle feed, jaggery, and ayurvedic herbs to keep Gau Mata energetic and satisfied.', 501.00, 'bi-heart-fill', 'https://images.unsplash.com/photo-1500595046743-cd271d694d30?auto=format&fit=crop&w=800&q=80', 1, 'active'),
(2, 'Adopt a Cow (Monthly)', 'adopt-a-cow', 'Sponsor the complete monthly living, feeding, shelter, and medical expenses of one cow.', 'Become a loving guardian for a cow of your choice. You will receive regular monthly photo updates, health reports, and spiritual blessings in your family name.', 2501.00, 'bi-award-fill', 'https://images.unsplash.com/photo-1546445317-29f4545e9d53?auto=format&fit=crop&w=800&q=80', 2, 'active'),
(3, 'Medical & Veterinary Seva', 'medical-seva', 'Fund specialized treatments, vaccinations, surgical supplies, and elderly cow care.', 'Supports our 24x7 veterinary hospital, on-call doctors, wound dressings, vitamin boosters, and emergency rescue operations for injured street cows.', 1001.00, 'bi-bandaid-fill', 'https://images.unsplash.com/photo-1570042225831-d98fa7577f1e?auto=format&fit=crop&w=800&q=80', 3, 'active'),
(4, 'Goushala Shelter Development', 'shelter-development', 'Build weather-proof sheds, clean drainage, eco-flooring, and solar lighting.', 'Helps expand our shelter capacity to rescue more cows, build hygienic water troughs, windbreaks for winter, and natural cooling misting systems for summer.', 5001.00, 'bi-house-heart-fill', 'https://images.unsplash.com/photo-1527153857715-3908f2ae5e81?auto=format&fit=crop&w=800&q=80', 4, 'active'),
(5, 'Green Fodder Land Support', 'fodder-support', 'Cultivate organic multi-cut Napier grass, sorghum, and Lucerne across 15 acres.', 'Enables sustainable round-the-year organic fodder farming with drip irrigation to ensure our cows never face a shortage of lush, pesticide-free green nutrition.', 2001.00, 'bi-tree-fill', 'https://images.unsplash.com/photo-1596733430284-f7437764b1a9?auto=format&fit=crop&w=800&q=80', 5, 'active'),
(6, 'Gopashtami & Special Gau Puja', 'gau-puja', 'Perform sacred Vedic Gau Puja and Archana on birthdays, anniversaries, and festivals.', 'Special Vedic rituals performed by priests honoring Gau Mata with flower garlands, sanctified prasad, sweet pongal, and chanting of Sri Kamadhenu Stotram in your name.', 1101.00, 'bi-stars', 'https://images.unsplash.com/photo-1516467508483-a7212febe31a?auto=format&fit=crop&w=800&q=80', 6, 'active');

-- 5. Testimonials
INSERT INTO `testimonials` (`id`, `author_name`, `designation`, `message`, `rating`, `avatar`, `status`, `display_order`) VALUES
(1, 'Dr. Rameshwar Sharma', 'Ayurvedic Physician & Donor', 'Visiting Kamadhenu Goushala was a deeply serene and purifying experience. The hygiene, love, and dedicated veterinary attention provided to indigenous breeds is world-class.', 5, 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=200&q=80', 'active', 1),
(2, 'Ananya Hegde', 'Bangalore, Tech Professional', 'I have adopted Nandini for over 2 years now. Getting monthly photo updates and spending weekends at the shelter with my children has connected our entire family to our Vedic roots.', 5, 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=200&q=80', 'active', 2),
(3, 'Venkatesh Murthy', 'Organic Farmer & Philanthropist', 'The high-quality Panchagavya and vermicompost produced here transformed my orchard. The commitment of the trustees to indigenous breed conservation is truly inspiring.', 5, 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=200&q=80', 'active', 3),
(4, 'Meenakshi Sundaram', 'Chennai, Lifelong Volunteer', 'The transparent reporting, prompt receipts for 80G tax benefit, and genuine care for abandoned cows make Kamadhenu Goushala our family’s primary charitable focus.', 5, 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=200&q=80', 'active', 4),
(5, 'Suresh Chandra Patel', 'Gujarat, Business Owner', 'Noble selfless service. The elderly cows receive medical treatment that is better than many human hospitals. May Bhagavan bless this sacred mission.', 5, 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=200&q=80', 'active', 5);

-- 6. Timeline
INSERT INTO `timeline` (`id`, `year`, `title`, `description`, `display_order`, `status`) VALUES
(1, '2015', 'Goushala Foundation', 'Started with humble shelter for 12 abandoned cows with a small team of passionate volunteers.', 1, 'active'),
(2, '2018', 'Shelter Expansion & Hospital', 'Inaugurated a dedicated 50-bed veterinary care facility, maternity ward, and feed storage godown.', 2, 'active'),
(3, '2021', 'Indigenous Breed Conservation', 'Established specialized preservation programs for native Gir, Hallikar, and Malenadu Gidda breeds.', 3, 'active'),
(4, '2024', 'Expanded Gau Seva & Solar Campus', 'Surpassed 500+ cows under care, installed 100% solar power grid, and 15 acres organic fodder farm.', 4, 'active'),
(5, '2026', 'Sustainable Organic Research Center', 'Pioneering organic Panchagavya production, zero-waste bio-energy, and community training workshops.', 5, 'active');

-- 7. Product Categories
INSERT INTO `product_categories` (`id`, `name`, `slug`, `description`, `status`) VALUES
(1, 'Organic Fertilizers', 'organic-fertilizers', 'Natural organic fertilizers made from sacred cow dung and farm biomass.', 'active'),
(2, 'Ayurvedic & Panchagavya', 'ayurvedic-panchagavya', 'Traditional herbal and Panchagavya wellness formulations.', 'active'),
(3, 'Spiritual & Puja Items', 'spiritual-puja-items', 'Pure cow dung diyas, sambrani dhoop, and sacred havan essentials.', 'active');

-- 8. Products
INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `description`, `price`, `stock`, `image`, `status`) VALUES
(1, 1, 'Organic Cow Dung Manure (5 kg)', 'organic-cow-dung-manure-5kg', '100% aged, sun-cured, and microbially rich cow dung manure. Perfect for home gardens, terrace vegetables, and flowering plants.', 199.00, 150, 'https://images.unsplash.com/photo-1585320806297-9794b3e4eeae?auto=format&fit=crop&w=800&q=80', 'active'),
(2, 1, 'Premium Vermicompost (10 kg)', 'premium-vermicompost-10kg', 'Enriched with beneficial soil microbes and earthworm castings. Boosts root aeration, moisture retention, and plant immunity.', 349.00, 120, 'https://images.unsplash.com/photo-1615485290382-441e4d049cb5?auto=format&fit=crop&w=800&q=80', 'active'),
(3, 2, 'Panchagavya Organic Bio-Booster (1 Liter)', 'panchagavya-bio-booster-1l', 'Traditional formulation prepared from 5 sacred cow outputs (milk, curd, ghee, urine, dung) enriched with banana, jaggery, and tender coconut water.', 280.00, 85, 'https://images.unsplash.com/photo-1608571423902-eed4a5ad8108?auto=format&fit=crop&w=800&q=80', 'active'),
(4, 3, 'Cow Dung Agnihotra Diyas (Pack of 21)', 'cow-dung-agnihotra-diyas', 'Handcrafted natural diyas that burn completely into sacred purifying ash. Releases positive energy and repels pests naturally during evening aarti.', 149.00, 200, 'https://images.unsplash.com/photo-1605651202774-7d573fd3f12d?auto=format&fit=crop&w=800&q=80', 'active'),
(5, 3, 'Herbal Desi Cow Ghee Dhoop Cones (30 Pcs)', 'herbal-cow-ghee-dhoop', 'Chemical-free natural dhoop infused with pure A2 cow ghee, guggul, camphor, and temple herbs for daily meditation and puja.', 175.00, 110, 'https://images.unsplash.com/photo-1603561596112-0a132b757442?auto=format&fit=crop&w=800&q=80', 'active'),
(6, 2, 'Distilled Gomutra Arka (500 ml)', 'distilled-gomutra-arka-500ml', 'Traditional multi-filtered steam-distilled Gomutra Arka prepared following Charaka Samhita guidelines from indigenous Gir cows.', 160.00, 95, 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?auto=format&fit=crop&w=800&q=80', 'active');

-- 9. Blog Categories
INSERT INTO `blog_categories` (`id`, `name`, `slug`, `status`) VALUES
(1, 'Indigenous Breeds', 'indigenous-breeds', 'active'),
(2, 'Vedic Wisdom & Science', 'vedic-wisdom-science', 'active'),
(3, 'Organic Agriculture', 'organic-agriculture', 'active'),
(4, 'Goushala Stories', 'goushala-stories', 'active');

-- 10. Blogs
INSERT INTO `blogs` (`id`, `category_id`, `title`, `slug`, `excerpt`, `content`, `featured_image`, `author`, `status`, `published_at`) VALUES
(1, 1, 'The Magnificent Significance of Indigenous Desi Cow Breeds', 'significance-of-indigenous-desi-cow-breeds', 'Discover why ancient Indian texts celebrate indigenous Bos Indicus cattle and how their genetic purity supports human health and ecological balance.', '<h2>Introduction to Indigenous Cattle Heritage</h2><p>India is blessed with an extraordinary diversity of native cattle breeds developed over thousands of years by our ancestors. Unlike exotic breeds, Indian Zebu cows (Bos Indicus) possess a distinctive hump, prominent dewlap, and sweat glands that confer superior heat tolerance and disease resistance.</p><h3>The Science of Surya Ketu Nadi</h3><p>Traditional Vedic lore describes the hump of indigenous cows as housing the Surya Ketu Nadi, which absorbs solar rays and infuses beneficial micronutrients and gold traces into cow milk and Panchagavya products.</p><h3>Why Conservation is Crucial Today</h3><p>Preserving native genetic stock is essential for food security, climate resilience, and natural zero-budget farming. Kamadhenu Goushala is proud to protect pure bloodlines of Gir, Hallikar, Malenadu Gidda, and Ongole breeds.</p>', 'https://images.unsplash.com/photo-1546445317-29f4545e9d53?auto=format&fit=crop&w=800&q=80', 'Acharya Vidyadhar', 'Published', '2026-06-15'),
(2, 2, 'A2 Milk vs A1 Milk: The Modern Scientific Perspective', 'a2-milk-vs-a1-milk-science', 'Explore the biochemical distinctions between A2 beta-casein found in Indian cows and A1 proteins, along with its digestive and cardiovascular advantages.', '<h2>Understanding Beta-Casein Proteins</h2><p>Milk proteins are primarily composed of caseins. The crucial difference between A1 and A2 milk lies at amino acid position 67 of the 209-amino-acid beta-casein chain. Desi cows naturally produce the ancestral A2 proline variant, which digests smoothly without releasing inflammatory peptides like BCM-7.</p><h3>Health Benefits of Native Cow Milk</h3><ul><li>Easier digestion and gut comfort</li><li>Natural immunoglobulins and antioxidants</li><li>Rich golden cream containing natural carotenoids</li><li>Balanced ratio of omega-3 and omega-6 fatty acids</li></ul>', 'https://images.unsplash.com/photo-1527153857715-3908f2ae5e81?auto=format&fit=crop&w=800&q=80', 'Dr. Rameshwar Sharma', 'Published', '2026-07-02'),
(3, 3, 'How Panchagavya Revitalizes Depleted Farm Soil', 'how-panchagavya-revitalizes-farm-soil', 'Learn how traditional organic formulations made from 5 sacred cow outputs trigger miraculous microbial activity and pest resistance in farming.', '<h2>The Alchemy of 5 Sacred Ingredients</h2><p>Panchagavya combines cow dung, fresh urine, milk, curd, and pure ghee, fermented with natural jaggery and tender coconut. The resulting liquid contains trillions of lactobacillus, yeasts, and nitrogen-fixing bacteria.</p><h3>Observed Agricultural Outcomes</h3><p>Farmers adopting Panchagavya report 25% higher yields, greater root depth, enhanced pest resilience, and substantial savings on synthetic fertilizers.</p>', 'https://images.unsplash.com/photo-1596733430284-f7437764b1a9?auto=format&fit=crop&w=800&q=80', 'Venkatesh Murthy', 'Published', '2026-07-20'),
(4, 4, 'Story of Nandini: From Distressed Rescue to Beloved Sanctuary Queen', 'story-of-nandini-rescue-to-queen', 'Read the heartwarming journey of Nandini, a gentle Gir mother cow who found refuge, healing, and lifelong care at Kamadhenu Goushala.', '<h2>A Miraculous Turnaround</h2><p>When our rescue ambulance brought Nandini to the shelter four years ago, she was severely dehydrated and underweight. With daily loving attention from our caregivers, herbal decoctions, and soothing Vedic chants, she made a full recovery.</p><p>Today she greets every visitor with friendly nudges and serves as a beacon of what compassionate seva can achieve.</p>', 'https://images.unsplash.com/photo-1500595046743-cd271d694d30?auto=format&fit=crop&w=800&q=80', 'Kamadhenu Seva Team', 'Published', '2026-08-01'),
(5, 2, 'The Sacred Ritual of Gopashtami: Honoring Gau Mata', 'sacred-ritual-of-gopashtami-honoring-gau-mata', 'Understanding the spiritual significance of Gopashtami festival when Lord Krishna officially took over cow care in the groves of Vrindavan.', '<h2>Lord Krishna and the Bovine Heritage</h2><p>On the auspicious day of Gopashtami, Bhagavan Sri Krishna was formally initiated into the sacred duty of taking cows for grazing. Worshipping Gau Mata on this day brings auspiciousness, peace, and family harmony.</p><p>Join us at Kamadhenu Goushala for our special annual Gopashtami Mahotsav and Maha Aarti.</p>', 'https://images.unsplash.com/photo-1516467508483-a7212febe31a?auto=format&fit=crop&w=800&q=80', 'Acharya Vidyadhar', 'Published', '2026-08-10');

-- 11. Events
INSERT INTO `events` (`id`, `title`, `slug`, `description`, `event_date`, `start_time`, `end_time`, `location`, `registration_url`, `image`, `status`) VALUES
(1, 'Annual Gopashtami Mahotsav & Gau Aarti', 'gopashtami-mahotsav-2026', 'Grand celebration featuring Maha Sudarshana Havan, 108 cow floral alankaram, cultural music, and grand community mahaprasadam.', '2026-11-18', '08:00:00', '16:00:00', 'Kamadhenu Goushala Main Grounds, Bengaluru', '#', 'https://images.unsplash.com/photo-1546445317-29f4545e9d53?auto=format&fit=crop&w=800&q=80', 'Upcoming'),
(2, 'Workshop on Organic Panchagavya Making', 'workshop-panchagavya-making', 'Hands-on practical training for organic farmers, terrace gardeners, and nature enthusiasts on brewing bio-boosters.', '2026-09-12', '09:30:00', '13:30:00', 'Kamadhenu Agriculture Center', '#', 'https://images.unsplash.com/photo-1615485290382-441e4d049cb5?auto=format&fit=crop&w=800&q=80', 'Upcoming'),
(3, 'Free Rural Veterinary Health Camp', 'free-veterinary-health-camp', 'Our team of veterinary doctors provides free checkups, deworming, and nutritional kits to 200+ local village cattle.', '2026-09-27', '08:30:00', '15:00:00', 'Hoskote Rural Outreach Center', '#', 'https://images.unsplash.com/photo-1570042225831-d98fa7577f1e?auto=format&fit=crop&w=800&q=80', 'Upcoming'),
(4, 'Monthly Gau Puja & Family Satsang', 'monthly-gau-puja-satsang', 'Spend a blissful Sunday morning performing Archana, feeding cows jaggery, and enjoying devotional bhajans.', '2026-09-06', '09:00:00', '12:00:00', 'Kamadhenu Temple Courtyard', '#', 'https://images.unsplash.com/photo-1527153857715-3908f2ae5e81?auto=format&fit=crop&w=800&q=80', 'Upcoming'),
(5, 'Indigenous Breeds Exhibition & Seminar', 'indigenous-breeds-seminar', 'An educational confluence exploring breed conservation, genetic biodiversity, and sustainable dairy models.', '2026-07-15', '10:00:00', '17:00:00', 'Convention Hall, Bengaluru', '#', 'https://images.unsplash.com/photo-1500595046743-cd271d694d30?auto=format&fit=crop&w=800&q=80', 'Completed');

-- 12. Gallery Categories
INSERT INTO `gallery_categories` (`id`, `name`, `slug`) VALUES
(1, 'Goushala Campus', 'goushala'),
(2, 'Our Cows', 'cows'),
(3, 'Events & Festivals', 'events'),
(4, 'Seva & Feeding', 'seva'),
(5, 'Volunteers', 'volunteers'),
(6, 'Festivals & Puja', 'festivals');

-- 13. Gallery Photos
INSERT INTO `gallery` (`id`, `category_id`, `title`, `image_url`, `display_order`, `status`) VALUES
(1, 2, 'Graceful Gir Mother with Newborn Calf', 'https://images.unsplash.com/photo-1546445317-29f4545e9d53?auto=format&fit=crop&w=800&q=80', 1, 'active'),
(2, 2, 'Majestic Hallikar Bull Balarama', 'https://images.unsplash.com/photo-1570042225831-d98fa7577f1e?auto=format&fit=crop&w=800&q=80', 2, 'active'),
(3, 1, 'Morning Sunshine over Green Grazing Meadows', 'https://images.unsplash.com/photo-1500595046743-cd271d694d30?auto=format&fit=crop&w=800&q=80', 3, 'active'),
(4, 4, 'Devotees Serving Fresh Green Napier Grass', 'https://images.unsplash.com/photo-1527153857715-3908f2ae5e81?auto=format&fit=crop&w=800&q=80', 4, 'active'),
(5, 2, 'Adorable Malenadu Gidda Dwarf Cow Gauri', 'https://images.unsplash.com/photo-1596733430284-f7437764b1a9?auto=format&fit=crop&w=800&q=80', 5, 'active'),
(6, 6, 'Sacred Gopashtami Floral Alankaram', 'https://images.unsplash.com/photo-1516467508483-a7212febe31a?auto=format&fit=crop&w=800&q=80', 6, 'active'),
(7, 3, 'Community Tree Planting and Fodder Drive', 'https://images.unsplash.com/photo-1597848212624-a19eb35e2651?auto=format&fit=crop&w=800&q=80', 7, 'active'),
(8, 5, 'Youth Volunteers at Weekend Cow Care Seva', 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=800&q=80', 8, 'active'),
(9, 1, 'Modern Hygienic Cow Shelter and Solar Water Troughs', 'https://images.unsplash.com/photo-1500595046743-cd271d694d30?auto=format&fit=crop&w=800&q=80', 9, 'active'),
(10, 4, 'Veterinary Doctor Treating Rescued Calf', 'https://images.unsplash.com/photo-1570042225831-d98fa7577f1e?auto=format&fit=crop&w=800&q=80', 10, 'active');

-- 14. Demo Donations
INSERT INTO `donations` (`id`, `donation_number`, `donor_name`, `donor_email`, `donor_phone`, `pan_number`, `amount`, `seva_id`, `seva_name`, `message`, `payment_method`, `razorpay_order_id`, `razorpay_payment_id`, `payment_status`, `created_at`) VALUES
(1, 'DON-202608-001', 'Arjun Nambiar', 'arjun.n@example.com', '+91 98450 12345', 'ABCDE1234F', 2501.00, 2, 'Adopt a Cow (Monthly)', 'For Nandini cow on my birthday. May all beings be happy.', 'Razorpay', 'order_demo_101', 'pay_demo_201', 'Completed', '2026-08-10 10:30:00'),
(2, 'DON-202608-002', 'Radha Krishna Rao', 'radha.krishna@example.com', '+91 99001 56789', 'BCDEF2345G', 5001.00, 4, 'Goushala Shelter Development', 'Towards building the new maternity ward.', 'Razorpay', 'order_demo_102', 'pay_demo_202', 'Completed', '2026-08-12 14:15:00'),
(3, 'DON-202608-003', 'Sunita Deshmukh', 'sunita.d@example.com', '+91 98112 34567', NULL, 501.00, 1, 'Feed a Cow for a Day', 'In loving memory of my grandmother.', 'UPI', 'order_demo_103', 'pay_demo_203', 'Completed', '2026-08-15 09:00:00'),
(4, 'DON-202608-004', 'Kishore Kumar V', 'kishore.v@example.com', '+91 94480 87654', 'CDEFG3456H', 10001.00, 3, 'Medical & Veterinary Seva', 'Support for emergency medicine kits.', 'Razorpay', 'order_demo_104', 'pay_demo_204', 'Completed', '2026-08-18 16:45:00'),
(5, 'DON-202608-005', 'Pooja Agarwal', 'pooja.a@example.com', '+91 97410 99887', NULL, 1001.00, 1, 'Feed a Cow for a Day', 'Om Namo Bhagavate Vasudevaya.', 'Razorpay', 'order_demo_105', NULL, 'Pending', '2026-08-20 11:20:00');

-- 15. Demo Sponsors & Sponsorships
INSERT INTO `sponsors` (`id`, `name`, `email`, `phone`, `pan_number`, `address`, `created_at`) VALUES
(1, 'Arjun Nambiar', 'arjun.n@example.com', '+91 98450 12345', 'ABCDE1234F', 'Indiranagar, Bengaluru, Karnataka', '2026-08-10 10:30:00'),
(2, 'Deepak Sreenivasan', 'deepak.s@example.com', '+91 98860 44332', 'DEFGH4567J', 'Jayanagar, Bengaluru, Karnataka', '2026-08-01 11:00:00');

INSERT INTO `sponsorships` (`id`, `sponsor_id`, `cow_id`, `duration_months`, `amount`, `start_date`, `end_date`, `payment_status`, `notes`) VALUES
(1, 1, 1, 12, 30000.00, '2026-08-01', '2027-07-31', 'Completed', 'Annual adoption of Nandini'),
(2, 2, 3, 6, 15000.00, '2026-08-01', '2027-01-31', 'Completed', 'Half-yearly adoption of Gauri');

-- 16. Contact Messages
INSERT INTO `contact_messages` (`id`, `name`, `email`, `phone`, `subject`, `message`, `status`, `created_at`) VALUES
(1, 'Vikramaditya Roy', 'vikram.roy@example.com', '+91 98200 11223', 'School Visit for 50 Students', 'Namaste, our school would like to arrange an educational excursion for grade 6 students to understand indigenous cows and organic farming. Please let us know the available dates.', 'New', '2026-08-19 14:20:00'),
(2, 'Gayathri Iyer', 'gayathri.iyer@example.com', '+91 94440 98765', 'Volunteering this Sunday', 'Hello, a group of 8 IT professionals would like to offer voluntary seva this Sunday morning. How can we register?', 'Read', '2026-08-18 10:10:00');

-- 17. Newsletter Subscribers
INSERT INTO `newsletter_subscribers` (`id`, `email`, `status`) VALUES
(1, 'devotee1@example.com', 'Active'),
(2, 'volunteer.blr@example.com', 'Active'),
(3, 'organic.gardener@example.com', 'Active');

-- 18. Website Settings
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_group`) VALUES
('site_name', 'Kamadhenu Goushala', 'general'),
('site_tagline', 'Serving Gau Mata With Pure Devotion & Vedic Care', 'general'),
('site_logo', '/img/logo.png', 'general'),
('phone_primary', '+91 98450 88990', 'contact'),
('phone_secondary', '+91 80 2845 6789', 'contact'),
('email_primary', 'info@kamadhenugoushala.org', 'contact'),
('email_donations', 'donations@kamadhenugoushala.org', 'contact'),
('address', 'Survey No. 48/2, Kamadhenu Valley, Near Sri Krishna Temple, Kanakapura Road, Bengaluru, Karnataka 560082, India', 'contact'),
('whatsapp_number', '919845088990', 'social'),
('facebook_url', 'https://facebook.com/kamadhenugoushala', 'social'),
('instagram_url', 'https://instagram.com/kamadhenugoushala', 'social'),
('youtube_url', 'https://youtube.com/@kamadhenugoushala', 'social'),
('twitter_url', 'https://twitter.com/kamadhenu_gau', 'social'),
('google_maps_url', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3889.3787754160416!2d77.53489817507487!3d12.883350487424075!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3bae3f7b2c0199cf%3A0x289758784d0b1a0!2sKanakapura%20Rd%2C%20Bengaluru%2C%20Karnataka!5e0!3m2!1sen!2sin!4v1700000000000!5m2!1sen!2sin', 'contact'),
('donation_upi_id', 'kamadhenu@sbi', 'donation'),
('donation_bank_name', 'State Bank of India', 'donation'),
('donation_account_name', 'Kamadhenu Goushala Seva Trust', 'donation'),
('donation_account_no', '3899010045678', 'donation'),
('donation_ifsc_code', 'SBIN0040123', 'donation'),
('donation_80g_info', 'Donations are eligible for 50% tax exemption under Section 80G of the Income Tax Act (Reg. No: AAATK1234PF20214).', 'donation'),
('stat_cows_served', '500+', 'stats'),
('stat_donors', '100+', 'stats'),
('stat_years_seva', '25+', 'stats'),
('stat_breeds', '10+', 'stats'),
('footer_about', 'Kamadhenu Goushala is a non-profit spiritual sanctuary dedicated to the ethical protection, preservation, and natural healthcare of indigenous Indian cow breeds (Desi Gau Mata).', 'general'),
('footer_copyright', '© 2026 Kamadhenu Goushala Trust. All rights reserved. Registered Public Charitable Trust.', 'general');
