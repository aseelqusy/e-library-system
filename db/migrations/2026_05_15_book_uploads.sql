-- Book uploads schema update
-- Adds cover image and PDF file path columns.

ALTER TABLE books
    ADD COLUMN cover_image VARCHAR(255) NULL AFTER description,
    ADD COLUMN pdf_file VARCHAR(255) NULL AFTER cover_image;

