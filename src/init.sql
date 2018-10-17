CREATE TABLE IF NOT EXISTS manufacturers (
  "id" INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  "name" TEXT NOT NULL UNIQUE,
  "url" TEXT DEFAULT NULL,
  "logo_url" TEXT DEFAULT NULL,
  "logo" TEXT DEFAULT NULL,
  "description" TEXT DEFAULT NULL,
  "count" INTEGER NOT NULL DEFAULT 0,
  "enabled" INTEGER NOT NULL DEFAULT 0
);

CREATE UNIQUE INDEX IF NOT EXISTS "uk_name"
ON "manufacturers" (
  "name"
);

CREATE TABLE IF NOT EXISTS categories (
  "id" INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  "category_id" INTEGER NOT NULL,
  "name" TEXT NOT NULL UNIQUE,
  "title" TEXT NOT NULL,
  "search_title" TEXT NOT NULL,
  "count" INTEGER NOT NULL DEFAULT 0,
  "level" INTEGER NOT NULL,
  "url" TEXT NOT NULL,
  "original_data" TEXT NOT NULL,
  "enabled" INTEGER NOT NULL DEFAULT 0
);

CREATE UNIQUE INDEX IF NOT EXISTS "uk_name"
ON "categories" (
  "name"
);

CREATE TABLE IF NOT EXISTS parts (
  "id" INTEGER PRIMARY KEY,
  "part_number" TEXT NOT NULL UNIQUE,
  "download_url" TEXT NOT NULL,
  "image_url" TEXT,
  "datasheet_url" TEXT NOT NULL,
  "npi" INTEGER NOT NULL,
  "promo_group" TEXT,
  "promo_group_key" TEXT,
  "category_id" TEXT NOT NULL,
  "parent_category_id" TEXT NOT NULL,
  "description" TEXT NOT NULL,
  "short_description" TEXT NOT NULL,
  "is_rohs_compliant" INTEGER NOT NULL,
  "attributes" TEXT NOT NULL,
  "part_url" TEXT NOT NULL,
  "manufacturer_id" TEXT NOT NULL,
  "feature_data" TEXT NOT NULL,
  "eccn_code" TEXT,
  "quantity" INTEGER NOT NULL,
  "original_data" TEXT NOT NULL
);

CREATE UNIQUE INDEX IF NOT EXISTS "uk_part_number"
ON "parts" (
  "part_number"
);

CREATE INDEX IF NOT EXISTS "idx_manufacturer_id"
ON "parts" (
  "manufacturer_id"
);

CREATE INDEX IF NOT EXISTS "idx_category_id"
ON "parts" (
  "parent_category_id",
  "category_id"
);

CREATE TABLE IF NOT EXISTS buying_options (
  "id" INTEGER PRIMARY KEY,
  "source_code" TEXT NOT NULL,
  "source_part_id" TEXT NOT NULL UNIQUE,
  "packaging_type" TEXT NOT NULL,
  "quantity" INTEGER NOT NULL,
  "manufacturer_lead_time" TINYINT NOT NULL,
  "date_code" INTEGER NOT NULL,
  "order_increment" INTEGER NOT NULL,
  "order_minimum_quantity" INTEGER NOT NULL,
  "pipeline_total" INTEGER,
  "ships_from_country_name" TEXT,
  "days_until_dispatch" INTEGER,
  "pipelines" TEXT NOT NULL,
  "price_bands" TEXT NOT NULL,
  "part_id" INTEGER NOT NULL,
  "inventory_region" TEXT NOT NULL,
  "unit_of_measure" TEXT NOT NULL,
  "origin_country_name" TEXT,
  "current_iso_currency" TEXT NOT NULL,
  "lowest_price" REAL,
  "highest_price" REAL,
  "original_data" TEXT NOT NULL
);

CREATE UNIQUE INDEX IF NOT EXISTS "uk_source_part_id"
ON "buying_options" (
  "source_part_id"
);

CREATE INDEX IF NOT EXISTS "idx_part_id"
ON "buying_options" (
  "part_id"
);