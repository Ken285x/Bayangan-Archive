-- IMPORTANT: Database and table identifiers below intentionally use the panel-required capitalization.
-- On Windows, the final visual case shown by phpMyAdmin is controlled by the MySQL
-- lower_case_table_names server setting. See DATABASE_CAPITALIZATION_SETUP.md.

-- BAYANGAN ARCHIVE CODE GUIDE
-- File: database.sql
-- Purpose: Creates and seeds the Bayangan Archive database.
-- Search for section comments before editing tables, game content, or Achievements.

CREATE DATABASE IF NOT EXISTS Bayangan_Archive CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE Bayangan_Archive;

CREATE TABLE Users(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(120) NOT NULL,email VARCHAR(190) NOT NULL UNIQUE,password VARCHAR(255) NOT NULL,role ENUM('admin') NOT NULL DEFAULT 'admin',status ENUM('active','inactive') NOT NULL DEFAULT 'active',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);

CREATE TABLE Pots(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(160) NOT NULL,pot_type VARCHAR(100) NOT NULL DEFAULT 'Other Pottery',description TEXT NOT NULL,uses_purpose TEXT,materials TEXT,cultural_significance TEXT,pot_maker VARCHAR(160),where_to_buy VARCHAR(255),estimated_price VARCHAR(120),contact_info VARCHAR(255),availability_note VARCHAR(255),image_path VARCHAR(500),video_path VARCHAR(500),status ENUM('draft','published','archived') DEFAULT 'draft',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP);

CREATE TABLE Learning_materials(id INT AUTO_INCREMENT PRIMARY KEY,category VARCHAR(100) NOT NULL,title VARCHAR(180) NOT NULL,content TEXT NOT NULL,image_path VARCHAR(500),video_path VARCHAR(500),sort_order INT DEFAULT 1,status ENUM('draft','published') DEFAULT 'published');
CREATE TABLE IF NOT EXISTS Tutorials(id INT AUTO_INCREMENT PRIMARY KEY,category VARCHAR(100) NOT NULL DEFAULT 'General',title VARCHAR(180) NOT NULL,description TEXT,content TEXT NOT NULL,image_path VARCHAR(500),video_path VARCHAR(500),sort_order INT NOT NULL DEFAULT 1,status ENUM('draft','published') NOT NULL DEFAULT 'published',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP);

-- Artisan/student/researcher account submission workflow was retired; public contributions use Knowledge_contributions.

CREATE TABLE Game_results(id INT AUTO_INCREMENT PRIMARY KEY,user_id INT NULL,guest_key VARCHAR(64) NULL,player_name VARCHAR(120) NULL,game_name VARCHAR(100) NOT NULL,difficulty ENUM('easy','medium','hard') NOT NULL DEFAULT 'medium',score INT DEFAULT 0,total INT DEFAULT 0,points_earned INT DEFAULT 0,hints_used INT DEFAULT 0,points_spent INT DEFAULT 0,played_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(user_id) REFERENCES Users(id) ON DELETE SET NULL);

CREATE TABLE User_points(id INT AUTO_INCREMENT PRIMARY KEY,user_id INT NOT NULL UNIQUE,balance INT NOT NULL DEFAULT 100,total_earned INT NOT NULL DEFAULT 100,total_spent INT NOT NULL DEFAULT 0,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,FOREIGN KEY(user_id) REFERENCES Users(id) ON DELETE CASCADE);

CREATE TABLE IF NOT EXISTS Jigsaw_puzzles (id INT AUTO_INCREMENT PRIMARY KEY,title VARCHAR(180) NOT NULL,description TEXT NULL,clue TEXT NULL,pot_id INT NULL,image_path VARCHAR(500) NOT NULL,status ENUM('draft','published') NOT NULL DEFAULT 'published',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,FOREIGN KEY(pot_id) REFERENCES Pots(id) ON DELETE SET NULL);
CREATE TABLE IF NOT EXISTS Achievements (id INT AUTO_INCREMENT PRIMARY KEY,code VARCHAR(80) NOT NULL UNIQUE,title VARCHAR(120) NOT NULL,description VARCHAR(255) NOT NULL,icon VARCHAR(20) NOT NULL DEFAULT '🏆');
CREATE TABLE IF NOT EXISTS User_achievements (id INT AUTO_INCREMENT PRIMARY KEY,user_id INT NOT NULL,achievement_id INT NOT NULL,earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,UNIQUE KEY uq_user_achievement(user_id,achievement_id),FOREIGN KEY(user_id) REFERENCES Users(id) ON DELETE CASCADE,FOREIGN KEY(achievement_id) REFERENCES Achievements(id) ON DELETE CASCADE);
INSERT IGNORE INTO Achievements(code,title,description,icon) VALUES ('jigsaw_first','Jigsaw Starter','Complete your first jigsaw puzzle.','🧩'),('jigsaw_three','Puzzle Explorer','Complete three jigsaw puzzles.','🗺️'),('jigsaw_perfect','Puzzle Master','Complete a jigsaw puzzle perfectly.','🏆'),('jigsaw_hard','Hard Mode Hero','Complete a Hard jigsaw puzzle.','🔥'),('jigsaw_speed','Speed Solver','Complete a jigsaw puzzle in two minutes or less.','⚡'),('game_first','Game Starter','Complete your first interactive game.','🎮'),('game_five','Game Explorer','Complete five interactive games.','🌟'),('game_perfect','Perfect Score','Complete a game with a perfect score.','💯'),('game_hard','Challenge Accepted','Complete a game on Hard difficulty.','🔥');

INSERT INTO Users(name,email,password,role,status) VALUES
('System Administrator','admin@bayangan.local','$2y$12$FhPfPJO8ei3ieRWiRUdGWuRFZgDFXXj7fhmLoj.zpEIv2WQ9V4bmK','admin','active');

INSERT INTO User_points(user_id,balance,total_earned,total_spent)
SELECT id,100,100,0 FROM Users
WHERE NOT EXISTS (SELECT 1 FROM User_points WHERE User_points.user_id = Users.id);

INSERT INTO Pots(name,description,uses_purpose,materials,cultural_significance,pot_maker,status) VALUES
('Traditional Clay Pot','A handmade clay vessel documented as an example of traditional pot-making practice.','Cooking, storing food or water, and household use.','Clay and locally available materials/tools used in preparation and shaping.','Represents local craftsmanship, cultural identity, and knowledge passed through community practice.','Local artisan — replace with verified name','published'),
('Storage Vessel','A traditional vessel shaped from prepared clay for storage.','Storage of food or household materials.','Prepared clay, shaping tools, and firing materials.','Demonstrates practical knowledge and continuity of traditional pottery.','Local artisan — replace with verified name','published');

INSERT INTO Learning_materials(category,title,content,sort_order,status) VALUES
('Materials & Tools','Materials and Tools','Traditional pot-making begins with clay and the tools used to prepare, shape, dry, and fire the vessel. The exact materials and tools should be documented from verified local artisan interviews.','1','published'),
('Clay Preparation','Preparation of Clay','Clay is prepared before shaping. The project flow identifies preparation of clay as a core stage. Bayangan Archive should record the local method, tools, source of clay, and artisan-specific knowledge through interviews and multimedia documentation.','2','published'),
('Process','Step-by-Step Pot-Making Process','The documented learning flow includes: preparation of clay, shaping techniques, drying process, firing process, and finishing/decorating where applicable. Each stage can be supported by photos, videos, and interviews.','3','published'),
('Technique','Shaping Techniques','Shaping is the stage where prepared clay is formed into the intended pot or vessel. The exact local technique should be captured from artisans rather than assumed, because traditional knowledge can vary by community and practitioner.','4','published'),
('Technique','Drying Process','After shaping, the pot is dried before firing. Document the local drying duration, location, handling practices, and indicators used by artisans to determine readiness.','5','published'),
('Technique','Firing Process','Firing is a major stage in transforming the shaped clay vessel into a durable pottery object. Bayangan Archive should document the local firing method, materials, sequence, and safety practices through verified field documentation.','6','published'),
('Culture','Cultural Value and Tradition','Traditional pottery reflects community knowledge, livelihood, creativity, cultural identity, and practices passed through generations. Digital preservation helps make this knowledge accessible to students, researchers, artisans, and future generations.','7','published');


INSERT INTO Tutorials(category,title,description,content,sort_order,status) VALUES
('Clay Preparation','Tutorial: Preparing Clay','A dedicated tutorial topic for documenting the local clay preparation process.','Use this record as a placeholder until the verified local artisan tutorial is entered. Document the source of clay, preparation steps, tools, duration, and artisan-specific practices from fieldwork.',1,'published'),
('Pot-Making Process','Tutorial: Shaping the Pot','A tutorial topic for documenting the local shaping technique.','Replace this placeholder with verified step-by-step instructions and the actual demonstration video from the documented pot-making practice.',2,'published'),
('Firing','Tutorial: Firing the Pot','A tutorial topic for documenting the local firing process.','Replace this placeholder with verified local firing instructions, materials, sequence, safety practices, and the actual demonstration video.',3,'published');

USE Bayangan_Archive;

CREATE TABLE IF NOT EXISTS Game_questions (
 id INT AUTO_INCREMENT PRIMARY KEY,
 game_type ENUM('identification','quiz') NOT NULL,
 difficulty ENUM('easy','medium','hard') NOT NULL DEFAULT 'medium',
 question TEXT NOT NULL,
 image_path VARCHAR(500) NULL,
 choice_a VARCHAR(255) NOT NULL,
 choice_b VARCHAR(255) NOT NULL,
 choice_c VARCHAR(255) NOT NULL,
 choice_d VARCHAR(255) NOT NULL,
 correct_choice CHAR(1) NOT NULL,
 explanation TEXT NULL,
 hint TEXT NULL,
 status ENUM('draft','published') NOT NULL DEFAULT 'published',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS Game_matching (
 id INT AUTO_INCREMENT PRIMARY KEY,
 left_text VARCHAR(255) NOT NULL,
 right_text VARCHAR(255) NOT NULL,
 left_image VARCHAR(500) NULL,
 right_image VARCHAR(500) NULL,
 difficulty ENUM('easy','medium','hard') NOT NULL DEFAULT 'medium',
 hint TEXT NULL,
 status ENUM('draft','published') NOT NULL DEFAULT 'published',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS Game_arrange (
 id INT AUTO_INCREMENT PRIMARY KEY,
 step_text VARCHAR(255) NOT NULL,
 image_path VARCHAR(500) NULL,
 difficulty ENUM('easy','medium','hard') NOT NULL DEFAULT 'medium',
 hint TEXT NULL,
 correct_order INT NOT NULL,
 status ENUM('draft','published') NOT NULL DEFAULT 'published',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- DYNAMIC GAME STARTER CONTENT
-- These are editable starter records for testing the game engine.
-- Replace them with verified Bayangan fieldwork data before final defense.
-- ============================================================

INSERT INTO Game_questions
(game_type, question, choice_a, choice_b, choice_c, choice_d, correct_choice, explanation, hint, status)
VALUES
('identification','Which material is central to traditional pot-making?','Clay','Plastic','Glass','Aluminum','A','Clay is the main material formed into pottery vessels.','Think about the material that can be shaped while moist.','published'),
('identification','Which stage gives prepared clay the intended vessel form?','Shaping','Advertising','Packing','Selling','A','Shaping forms the prepared clay into the intended vessel.','This happens after clay preparation and before drying.','published'),
('identification','Which stage removes moisture before the pottery is fired?','Drying','Decorating','Selling','Storing','A','Drying reduces moisture before the firing stage.','Think about what must happen before strong heat is applied.','published'),
('identification','Which stage uses heat to harden the shaped clay?','Firing','Washing','Packing','Measuring','A','Firing uses heat to transform the dried clay vessel into a more durable object.','This stage involves heat.','published'),
('identification','Which item belongs in a digital pottery record?','Pottery description','Random advertisement','Unrelated movie review','Game cheat code','A','A pottery record should contain relevant documentation such as descriptions and verified cultural information.','Choose the item that documents the pottery itself.','published'),
('identification','Which media type can document the appearance of an actual pot?','Photo','Password','SQL error','Session ID','A','Photos can visually document pottery objects and processes.','It is a visual form of documentation.','published'),
('identification','Which media type is useful for showing a pot-making process from start to finish?','Video','Username','Database key','CSS class','A','Video can document movement, sequence, and technique during the process.','It records actions over time.','published'),
('identification','Which stage normally comes before firing in the documented learning flow?','Drying','Selling','Archiving','Registration','A','The documented learning flow places drying before firing.','The vessel should lose moisture first.','published'),
('identification','Who should be a key source when documenting traditional pot-making knowledge?','Local artisan or pot maker','Random online gamer','Unrelated salesperson','Anonymous bot','A','Traditional knowledge should be documented from appropriate knowledgeable sources such as local artisans.','Think about who actually practices the craft.','published'),
('identification','Which system feature helps preserve pottery information for later retrieval?','Digital archive','Calculator only','Music player','Weather widget','A','A digital archive stores documented knowledge so it can be organized and retrieved.','It is the central purpose of Bayangan Archive.','published');

INSERT INTO Game_questions
(game_type, question, choice_a, choice_b, choice_c, choice_d, correct_choice, explanation, hint, status)
VALUES
('quiz','What is the purpose of drying a shaped clay pot before firing?','To reduce moisture','To add a password','To create a user account','To rename a database','A','Drying reduces moisture before the firing process.','Think about water remaining in the shaped clay.','published'),
('quiz','What is the main purpose of the Bayangan Archive?','Preserve and document traditional pot-making knowledge','Sell unrelated products','Replace artisan knowledge','Store movies','A','The system is designed to preserve and digitally document traditional pot-making practices.','Focus on preservation and documentation.','published'),
('quiz','Which combination best supports multimedia documentation?','Text, images, and videos','Passwords, cookies, and ads','Only usernames','Only numbers','A','The project uses text, images, and videos for digital documentation.','Think of three common media formats.','published'),
('quiz','Why should game questions be managed from the database?','So content can be updated without editing game source code','So every game stays identical forever','So Users cannot play','So images are disabled','A','Database-driven content lets authorized Users update published questions dynamically.','Think about what makes the system dynamic.','published'),
('quiz','What should happen to the order of questions in a randomized game?','It should vary between plays','It should always start with the same question','It should be hardcoded permanently','It should contain only one item','A','Randomization changes the question order so repeated plays are less predictable.','The sequence should not always be the same.','published'),
('quiz','What can earned game points be used for in this system?','Buying hints','Deleting Users','Changing admin passwords','Dropping the database','A','The points economy allows players to spend points on hints.','It helps the player when stuck.','published'),
('quiz','Which record is useful for tracking learning-game progress?','Score and game attempt history','Browser wallpaper','Monitor brightness','Keyboard brand','A','Scores and saved game attempts provide measurable progress records.','Think about information shown in the Progress page.','published'),
('quiz','Who can add or edit game content through the Game CMS?','Authorized admin','Every anonymous visitor','Any search engine','Nobody','A','The Game Content Management page is an administrative function.','It is located in the Admin area.','published'),
('quiz','What should be done with starter demo questions before final defense?','Replace or verify them using actual fieldwork data','Claim all are field interview results','Delete the whole database','Make them unrelated to pottery','A','Starter data is for testing; final content should be based on verified project documentation.','Think about research validity.','published'),
('quiz','Which stage follows shaping in the basic documented process used by the starter learning flow?','Drying','Login','Registration','Search','A','The starter learning flow uses preparation, shaping, drying, and firing.','It happens before firing.','published');

INSERT INTO Game_matching(left_text,right_text,hint,status) VALUES
('Traditional clay pot photo','Traditional Clay Pot','Look at the overall vessel shape and traditional clay finish.','published'),
('Storage vessel photo','Storage Vessel','This vessel is associated with storage use.','published'),
('Traditional pot photo','Traditional Clay Pot','Match the image with the exact pottery name.','published'),
('Storage pot photo','Storage Vessel','Match the image with the exact pottery name.','published'),
('Bayangan pottery photo','Traditional Clay Pot','Identify the pottery name shown in the picture.','published'),
('Documented storage pot photo','Storage Vessel','Identify the correct documented pot name.','published');

INSERT INTO Game_arrange(step_text,correct_order,hint,status) VALUES
('Prepare the clay',1,'This is the starting stage before shaping.','published'),
('Shape the prepared clay',2,'Form the vessel after preparation.','published'),
('Dry the shaped vessel',3,'Reduce moisture before applying high heat.','published'),
('Fire the dried vessel',4,'This is the heat-based stage.','published'),
('Document the finished pottery',5,'Record the result using verified text, photos, or videos.','published');

-- Seed demo records across all three difficulty levels. Replace/verify with actual fieldwork content.
UPDATE Game_questions SET difficulty = CASE MOD(id,3) WHEN 1 THEN 'easy' WHEN 2 THEN 'medium' ELSE 'hard' END;
UPDATE Game_matching SET difficulty = CASE MOD(id,3) WHEN 1 THEN 'easy' WHEN 2 THEN 'medium' ELSE 'hard' END;
UPDATE Game_arrange SET difficulty = CASE MOD(id,3) WHEN 1 THEN 'easy' WHEN 2 THEN 'medium' ELSE 'hard' END;

-- THESIS-LEVEL EXTENSIONS: provenance, learning progress, Favorites, Notifications, audit/activity, views.
CREATE TABLE IF NOT EXISTS Archive_metadata (
 id INT AUTO_INCREMENT PRIMARY KEY, pot_id INT NOT NULL UNIQUE, local_name VARCHAR(160) NULL, location VARCHAR(180) NULL, documented_date DATE NULL, source_type VARCHAR(120) NULL, source_name VARCHAR(180) NULL, verification_status ENUM('pending','verified','needs_review') NOT NULL DEFAULT 'pending', documented_by VARCHAR(180) NULL, version_label VARCHAR(40) NOT NULL DEFAULT '1.0', notes TEXT NULL, latitude DECIMAL(10,7) NULL, longitude DECIMAL(10,7) NULL, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, FOREIGN KEY(pot_id) REFERENCES Pots(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS Learning_progress (
 id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, material_id INT NULL, tutorial_id INT NULL, completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY uq_learning_item(user_id,material_id,tutorial_id), FOREIGN KEY(user_id) REFERENCES Users(id) ON DELETE CASCADE, FOREIGN KEY(material_id) REFERENCES Learning_materials(id) ON DELETE CASCADE, FOREIGN KEY(tutorial_id) REFERENCES Tutorials(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS Favorites (
 id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, pot_id INT NULL, tutorial_id INT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY uq_favorite(user_id,pot_id,tutorial_id), FOREIGN KEY(user_id) REFERENCES Users(id) ON DELETE CASCADE, FOREIGN KEY(pot_id) REFERENCES Pots(id) ON DELETE CASCADE, FOREIGN KEY(tutorial_id) REFERENCES Tutorials(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS Notifications (
 id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NULL, title VARCHAR(180) NOT NULL, message TEXT NOT NULL, type VARCHAR(50) NOT NULL DEFAULT 'info', is_read TINYINT(1) NOT NULL DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY(user_id) REFERENCES Users(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS Activity_logs (
 id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NULL, actor_name VARCHAR(120) NULL, actor_role VARCHAR(30) NULL, action VARCHAR(160) NOT NULL, details TEXT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY(user_id) REFERENCES Users(id) ON DELETE SET NULL
);

INSERT IGNORE INTO Archive_metadata(pot_id,source_type,source_name,verification_status,documented_by,version_label) SELECT id,'Demo/Test Data','Starter record','needs_review','Replace with verified fieldwork source','1.0' FROM Pots;


-- Public interaction tables
CREATE TABLE IF NOT EXISTS Pot_comments (id INT AUTO_INCREMENT PRIMARY KEY,pot_id INT NOT NULL,parent_id INT NULL,visitor_name VARCHAR(120) NOT NULL,comment TEXT NOT NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(pot_id) REFERENCES Pots(id) ON DELETE CASCADE,FOREIGN KEY(parent_id) REFERENCES Pot_comments(id) ON DELETE CASCADE) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS Community_questions (id INT AUTO_INCREMENT PRIMARY KEY,visitor_name VARCHAR(120) NOT NULL,question TEXT NOT NULL,answer TEXT NULL,answered_by INT NULL,status ENUM('pending','answered') NOT NULL DEFAULT 'pending',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,answered_at DATETIME NULL,FOREIGN KEY(answered_by) REFERENCES Users(id) ON DELETE SET NULL) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS Knowledge_contributions (id INT AUTO_INCREMENT PRIMARY KEY,visitor_name VARCHAR(120) NOT NULL,title VARCHAR(180) NOT NULL,content TEXT NOT NULL,source VARCHAR(255) NULL,image_path VARCHAR(500) NULL,status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',reviewed_by INT NULL,reviewed_at DATETIME NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(reviewed_by) REFERENCES Users(id) ON DELETE SET NULL) ENGINE=InnoDB;

-- Public interaction and demand analytics additions (safe for fresh installs; config/db.php migrates existing installs).
CREATE TABLE IF NOT EXISTS Pot_ratings (id INT AUTO_INCREMENT PRIMARY KEY,pot_id INT NOT NULL,visitor_name VARCHAR(120) NOT NULL,rating TINYINT NOT NULL,review TEXT NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(pot_id) REFERENCES Pots(id) ON DELETE CASCADE,INDEX idx_rating_pot(pot_id)) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS Pot_corrections (id INT AUTO_INCREMENT PRIMARY KEY,pot_id INT NOT NULL,visitor_name VARCHAR(120) NOT NULL,suggestion TEXT NOT NULL,status ENUM('pending','accepted','rejected') NOT NULL DEFAULT 'pending',reviewed_by INT NULL,reviewed_at DATETIME NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(pot_id) REFERENCES Pots(id) ON DELETE CASCADE,FOREIGN KEY(reviewed_by) REFERENCES Users(id) ON DELETE SET NULL) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS Pot_views (id BIGINT AUTO_INCREMENT PRIMARY KEY,pot_id INT NOT NULL,visitor_key VARCHAR(80) NULL,viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(pot_id) REFERENCES Pots(id) ON DELETE CASCADE,INDEX idx_views_pot(pot_id),INDEX idx_views_date(viewed_at)) ENGINE=InnoDB;
ALTER TABLE Users ADD COLUMN IF NOT EXISTS admin_level ENUM('super','content','review') NOT NULL DEFAULT 'content' AFTER role;
UPDATE Users SET admin_level='super' WHERE role='admin' AND id=(SELECT min_id FROM (SELECT MIN(id) min_id FROM Users WHERE role='admin') x);

CREATE TABLE IF NOT EXISTS Pot_versions (id BIGINT AUTO_INCREMENT PRIMARY KEY,pot_id INT NOT NULL,version_label VARCHAR(40) NOT NULL,snapshot LONGTEXT NOT NULL,changed_by INT NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(pot_id) REFERENCES Pots(id) ON DELETE CASCADE,FOREIGN KEY(changed_by) REFERENCES Users(id) ON DELETE SET NULL) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS Comment_reports (id BIGINT AUTO_INCREMENT PRIMARY KEY,comment_id INT NOT NULL,visitor_name VARCHAR(120) NOT NULL,reason VARCHAR(120) NOT NULL,details TEXT NULL,status ENUM('open','resolved','dismissed') NOT NULL DEFAULT 'open',reviewed_by INT NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(comment_id) REFERENCES Pot_comments(id) ON DELETE CASCADE,FOREIGN KEY(reviewed_by) REFERENCES Users(id) ON DELETE SET NULL) ENGINE=InnoDB;


-- Traditional Terminology: Bayangan local pottery vocabulary with pronunciation audio
CREATE TABLE IF NOT EXISTS Traditional_terms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    term VARCHAR(180) NOT NULL,
    pronunciation VARCHAR(255) NULL,
    meaning TEXT NOT NULL,
    category VARCHAR(120) NOT NULL DEFAULT 'General',
    context_example TEXT NULL,
    source_informant VARCHAR(180) NULL,
    fieldwork_note TEXT NULL,
    audio_path VARCHAR(500) NULL,
    status ENUM('draft','published') NOT NULL DEFAULT 'published',
    sort_order INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_terms_status(status),
    INDEX idx_terms_category(category),
    INDEX idx_terms_term(term)
) ENGINE=InnoDB;


-- INTERACTIVE LEARNING UPGRADE: process steps, heritage quests, artisan stories, feedback, and engagement analytics.
ALTER TABLE Traditional_terms ADD COLUMN IF NOT EXISTS english_meaning TEXT NULL AFTER meaning;
ALTER TABLE Traditional_terms ADD COLUMN IF NOT EXISTS filipino_meaning TEXT NULL AFTER english_meaning;
ALTER TABLE Traditional_terms ADD COLUMN IF NOT EXISTS photo_path VARCHAR(500) NULL AFTER fieldwork_note;
ALTER TABLE Traditional_terms ADD COLUMN IF NOT EXISTS verification_status ENUM('pending','verified','needs_review') NOT NULL DEFAULT 'pending' AFTER status;
CREATE TABLE IF NOT EXISTS Interactive_steps(id INT AUTO_INCREMENT PRIMARY KEY,title VARCHAR(180) NOT NULL,short_description VARCHAR(255) NULL,detail TEXT NOT NULL,image_path VARCHAR(500) NULL,video_path VARCHAR(500) NULL,term_id INT NULL,sort_order INT NOT NULL DEFAULT 1,status ENUM('draft','published') NOT NULL DEFAULT 'published',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,FOREIGN KEY(term_id) REFERENCES Traditional_terms(id) ON DELETE SET NULL) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS Heritage_quests(id INT AUTO_INCREMENT PRIMARY KEY,title VARCHAR(180) NOT NULL,description TEXT NOT NULL,target_type VARCHAR(60) NOT NULL DEFAULT 'explore',target_value VARCHAR(120) NULL,points INT NOT NULL DEFAULT 25,badge VARCHAR(120) NULL,sort_order INT NOT NULL DEFAULT 1,status ENUM('draft','published') NOT NULL DEFAULT 'published',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS Artisan_stories(id INT AUTO_INCREMENT PRIMARY KEY,pot_maker VARCHAR(180) NOT NULL,question VARCHAR(255) NOT NULL,answer TEXT NOT NULL,audio_path VARCHAR(500) NULL,image_path VARCHAR(500) NULL,sort_order INT NOT NULL DEFAULT 1,status ENUM('draft','published') NOT NULL DEFAULT 'published',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS Knowledge_feedback(id BIGINT AUTO_INCREMENT PRIMARY KEY,visitor_name VARCHAR(120) NULL,content_type VARCHAR(60) NOT NULL,content_id INT NULL,rating TINYINT NOT NULL,feedback TEXT NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS Interactive_events(id BIGINT AUTO_INCREMENT PRIMARY KEY,visitor_key VARCHAR(80) NOT NULL,event_type VARCHAR(80) NOT NULL,content_type VARCHAR(60) NULL,content_id INT NULL,details VARCHAR(255) NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,INDEX idx_events_type(event_type),INDEX idx_events_date(created_at)) ENGINE=InnoDB;


-- THESIS UPGRADE: source management, artisan profiles, learning paths, verification/audit.
CREATE TABLE IF NOT EXISTS Pot_sources (
 id INT AUTO_INCREMENT PRIMARY KEY,pot_id INT NOT NULL,source_type VARCHAR(100) NOT NULL,source_title VARCHAR(180) NULL,
 informant VARCHAR(180) NULL,documented_date DATE NULL,location VARCHAR(180) NULL,documenter VARCHAR(180) NULL,
 citation_text TEXT NULL,verification_status ENUM('pending','verified','needs_review') NOT NULL DEFAULT 'pending',notes TEXT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY(pot_id) REFERENCES Pots(id) ON DELETE CASCADE,INDEX idx_source_pot(pot_id),INDEX idx_source_status(verification_status)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS Artisan_profiles (
 id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(180) NOT NULL UNIQUE,biography TEXT NULL,years_experience VARCHAR(80) NULL,
 specialization VARCHAR(255) NULL,location VARCHAR(180) NULL,story TEXT NULL,image_path VARCHAR(500) NULL,audio_path VARCHAR(500) NULL,
 status ENUM('draft','published','archived') NOT NULL DEFAULT 'published',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS Learning_paths (
 id INT AUTO_INCREMENT PRIMARY KEY,title VARCHAR(180) NOT NULL,description TEXT NULL,
 level ENUM('beginner','intermediate','advanced') NOT NULL DEFAULT 'beginner',status ENUM('draft','published') NOT NULL DEFAULT 'published',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS Learning_path_items (
 id INT AUTO_INCREMENT PRIMARY KEY,path_id INT NOT NULL,item_type VARCHAR(50) NOT NULL,item_id INT NOT NULL,sort_order INT NOT NULL DEFAULT 1,
 FOREIGN KEY(path_id) REFERENCES Learning_paths(id) ON DELETE CASCADE,INDEX idx_path_items(path_id,sort_order)
) ENGINE=InnoDB;
-- Knowledge_reviews was part of the retired Verification Center and is not created for new installs.
INSERT IGNORE INTO Learning_paths(title,description,level,status) VALUES
('Bayangan Pottery Foundations','A guided introduction to materials, tools, process, culture, and documented pottery knowledge.','beginner','published'),
('Traditional Pottery Explorer','A deeper learning path using Tutorials, terminology, stories, and interactive activities.','intermediate','published');

-- Guided learning path starter assignments. Admin can replace these with verified curriculum.
INSERT IGNORE INTO Learning_path_items(path_id,item_type,item_id,sort_order)
SELECT lp.id,'learning',lm.id,lm.sort_order FROM Learning_paths lp JOIN Learning_materials lm ON lp.level='beginner' AND lm.status='published' LIMIT 6;
INSERT IGNORE INTO Learning_path_items(path_id,item_type,item_id,sort_order)
SELECT lp.id,'tutorial',t.id,t.sort_order FROM Learning_paths lp JOIN Tutorials t ON lp.level='intermediate' AND t.status='published' LIMIT 3;


-- PUBLIC LEARNER PROFILE: anonymous progress without mandatory accounts.
CREATE TABLE IF NOT EXISTS Public_players (
 guest_key VARCHAR(64) PRIMARY KEY,player_name VARCHAR(120) NOT NULL DEFAULT 'Guest Player',recovery_hash VARCHAR(255) NULL,xp INT NOT NULL DEFAULT 0,
 total_earned INT NOT NULL DEFAULT 0,total_spent INT NOT NULL DEFAULT 0,current_streak INT NOT NULL DEFAULT 0,last_challenge_date DATE NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,last_seen_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_public_players_xp(xp),INDEX idx_public_players_name(player_name)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS Public_player_devices (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,guest_key VARCHAR(64) NOT NULL,token_hash CHAR(64) NOT NULL UNIQUE,expires_at DATETIME NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,last_used_at TIMESTAMP NULL DEFAULT NULL,INDEX idx_player_device_guest(guest_key),INDEX idx_player_device_expiry(expires_at)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS Public_interactive_progress (guest_key VARCHAR(64) PRIMARY KEY,state_json LONGTEXT NOT NULL,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS Public_saved_pots (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,guest_key VARCHAR(64) NOT NULL,pot_id INT NOT NULL,
 saved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,UNIQUE KEY uq_public_saved_pot(guest_key,pot_id),
 INDEX idx_public_saved_guest(guest_key),INDEX idx_public_saved_pot(pot_id)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS Public_learning_path_progress (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,guest_key VARCHAR(64) NOT NULL,path_id INT NOT NULL,item_key VARCHAR(80) NOT NULL,
 completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,UNIQUE KEY uq_public_path_item(guest_key,path_id,item_key),
 INDEX idx_public_path_guest(guest_key,path_id)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS Public_learning_progress (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,guest_key VARCHAR(64) NOT NULL,material_id INT NULL,tutorial_id INT NULL,
 completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,UNIQUE KEY uq_public_learning(guest_key,material_id,tutorial_id),INDEX idx_public_learning_guest(guest_key)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS Public_challenge_attempts (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,guest_key VARCHAR(64) NOT NULL,challenge_date DATE NOT NULL,question_id INT NOT NULL,
 correct TINYINT(1) NOT NULL DEFAULT 0,points INT NOT NULL DEFAULT 0,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_public_challenge(guest_key,challenge_date),INDEX idx_public_challenge_date(challenge_date)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS Public_player_achievements (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,guest_key VARCHAR(64) NOT NULL,achievement_id INT NOT NULL,earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_public_achievement(guest_key,achievement_id),INDEX idx_public_achievement_guest(guest_key),
 FOREIGN KEY(achievement_id) REFERENCES Achievements(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Many-to-many mapping: one pottery record can belong to multiple Admin-managed categories.
CREATE TABLE IF NOT EXISTS Pot_categories(pot_id INT NOT NULL,category_id INT NOT NULL,PRIMARY KEY(pot_id,category_id),INDEX idx_pot_categories_category(category_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- PUBLIC ARTISAN POTTERY SUBMISSIONS: reviewed before conversion into published Pots records.
CREATE TABLE IF NOT EXISTS Artisan_pottery_submissions (
 id INT AUTO_INCREMENT PRIMARY KEY,
 artisan_name VARCHAR(160) NOT NULL, artisan_email VARCHAR(190) NULL,
 name VARCHAR(160) NOT NULL, pot_type VARCHAR(100) NOT NULL DEFAULT 'Other Pottery',
 local_name VARCHAR(160) NULL, description TEXT NOT NULL, uses_purpose TEXT NULL,
 materials TEXT NULL, cultural_significance TEXT NULL, pot_maker VARCHAR(160) NULL,
 where_to_buy VARCHAR(255) NULL, estimated_price VARCHAR(120) NULL, contact_info VARCHAR(255) NULL,
 availability_note VARCHAR(255) NULL, location VARCHAR(180) NULL, documented_date DATE NULL,
 source_type VARCHAR(120) NULL, source_name VARCHAR(180) NULL, documented_by VARCHAR(180) NULL,
 notes TEXT NULL, image_path VARCHAR(500) NULL, video_path VARCHAR(500) NULL,
 status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
 reviewed_by INT NULL, reviewed_at DATETIME NULL, resulting_pot_id INT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_artisan_submission_status(status),
 FOREIGN KEY(reviewed_by) REFERENCES Users(id) ON DELETE SET NULL,
 FOREIGN KEY(resulting_pot_id) REFERENCES Pots(id) ON DELETE SET NULL
) ENGINE=InnoDB;
