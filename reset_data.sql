-- Reset dữ liệu và import lại

-- Xóa dữ liệu cũ
DELETE FROM vote_responses;
DELETE FROM vote_options;
DELETE FROM votes;
DELETE FROM events;
DELETE FROM maintenances;
DELETE FROM devices;
DELETE FROM payments;
DELETE FROM invoices;
DELETE FROM feedbacks;
DELETE FROM notification_recipients;
DELETE FROM notifications;
DELETE FROM residents;
DELETE FROM apartments;
DELETE FROM users;

-- Reset auto increment
ALTER TABLE users AUTO_INCREMENT = 1;
ALTER TABLE apartments AUTO_INCREMENT = 1;
ALTER TABLE residents AUTO_INCREMENT = 1;
ALTER TABLE notifications AUTO_INCREMENT = 1;
ALTER TABLE notification_recipients AUTO_INCREMENT = 1;
ALTER TABLE feedbacks AUTO_INCREMENT = 1;
ALTER TABLE invoices AUTO_INCREMENT = 1;
ALTER TABLE payments AUTO_INCREMENT = 1;
ALTER TABLE devices AUTO_INCREMENT = 1;
ALTER TABLE maintenances AUTO_INCREMENT = 1;
ALTER TABLE events AUTO_INCREMENT = 1;
ALTER TABLE votes AUTO_INCREMENT = 1;
ALTER TABLE vote_options AUTO_INCREMENT = 1;
ALTER TABLE vote_responses AUTO_INCREMENT = 1;
