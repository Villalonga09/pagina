-- Crear esquema
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin') NOT NULL DEFAULT 'admin',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE raffles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(180) NOT NULL,
  description TEXT,
  prize VARCHAR(180) NOT NULL,
  price_usd DECIMAL(10,2) NOT NULL,
  price_ves DECIMAL(12,2) NOT NULL,
  total_tickets INT NOT NULL,
  sold_tickets INT NOT NULL DEFAULT 0,
  banner_path VARCHAR(255),
  status ENUM('borrador','activa','finalizada') DEFAULT 'activa',
  starts_at DATETIME NULL,
  ends_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE tickets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  raffle_id INT NOT NULL,
  number INT NOT NULL,
  status ENUM('disponible','reservado','vendido') DEFAULT 'disponible',
  order_id INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_raffle_number (raffle_id, number),
  INDEX idx_raffle_status (raffle_id, status),
  CONSTRAINT fk_tickets_raffles FOREIGN KEY (raffle_id) REFERENCES raffles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(20) NOT NULL UNIQUE,
  buyer_name VARCHAR(160) NOT NULL,
  buyer_email VARCHAR(160) NOT NULL,
  buyer_phone VARCHAR(40),
  buyer_dni VARCHAR(40) NOT NULL,
  total_usd DECIMAL(10,2) NOT NULL,
  total_ves DECIMAL(12,2) NOT NULL,
  status ENUM('pendiente','pagado','rechazado') DEFAULT 'pendiente',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  raffle_id INT NOT NULL,
  ticket_id INT NOT NULL,
  price_usd DECIMAL(10,2) NOT NULL,
  price_ves DECIMAL(12,2) NOT NULL,
  CONSTRAINT fk_items_orders FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  CONSTRAINT fk_items_raffles FOREIGN KEY (raffle_id) REFERENCES raffles(id),
  CONSTRAINT fk_items_tickets FOREIGN KEY (ticket_id) REFERENCES tickets(id)
) ENGINE=InnoDB;

CREATE TABLE payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  method ENUM('pago_movil','zelle','binance','efectivo') DEFAULT 'pago_movil',
  amount_ves DECIMAL(12,2) NULL,
  amount_usd DECIMAL(10,2) NULL,
  reference VARCHAR(120) NULL,
  receipt_path VARCHAR(255) NULL,
  status ENUM('pendiente','aprobado','rechazado') DEFAULT 'pendiente',
  reviewed_by INT NULL,
  reviewed_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_payments_orders FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  CONSTRAINT fk_payments_users FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE activity_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  action VARCHAR(60) NOT NULL,
  entity_type VARCHAR(60) NOT NULL,
  entity_id INT NOT NULL,
  message VARCHAR(255) NOT NULL,
  meta_json JSON NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_entity (entity_type, entity_id, created_at DESC),
  CONSTRAINT fk_activity_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  skey VARCHAR(120) UNIQUE NOT NULL,
  svalue TEXT NOT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Seeds
INSERT INTO raffles (title,description,prize,price_usd,price_ves,total_tickets,sold_tickets,banner_path,status,starts_at,ends_at) VALUES
('Rifa iPhone 14','Participa por un iPhone 14 nuevo','iPhone 14',5.00,200.00,100,0,NULL,'activa',NOW(),DATE_ADD(NOW(), INTERVAL 30 DAY)),
('Rifa PlayStation 5','Gana una PS5 edición digital','PlayStation 5',4.00,160.00,80,0,NULL,'activa',NOW(),DATE_ADD(NOW(), INTERVAL 30 DAY));

-- Generar tickets para las rifas iniciales (50/80)
SET @rid1 = (SELECT id FROM raffles WHERE title='Rifa iPhone 14' LIMIT 1);
SET @rid2 = (SELECT id FROM raffles WHERE title='Rifa PlayStation 5' LIMIT 1);

-- Inserta 100 boletos para rid1
DELIMITER //
CREATE PROCEDURE seed_tickets(IN raffleId INT, IN total INT)
BEGIN
  DECLARE i INT DEFAULT 1;
  WHILE i <= total DO
    INSERT INTO tickets(raffle_id, number, status) VALUES(raffleId, i, 'disponible');
    SET i = i + 1;
  END WHILE;
END //
DELIMITER ;

CALL seed_tickets(@rid1, 100);
CALL seed_tickets(@rid2, 80);
DROP PROCEDURE seed_tickets;

INSERT INTO settings (skey,svalue) VALUES ('bcv_rate','40');
