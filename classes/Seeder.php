<?php

declare(strict_types=1);

/**
 * One-time (and safely repeatable) data seeding.
 *
 *  - seedAdmin(): inserts the configured administrator only when the
 *    admins table is empty. Existing accounts are never overwritten.
 *  - seedTrips(): inserts the seven existing journeys from the public
 *    site as starter content, only when the trips table is empty.
 *  - seedGalleryPhotos(): inserts 28 starter photographs (four for each
 *    of the seven albums) from the existing /images folders, only when
 *    gallery_photos is empty.
 */
final class Seeder
{
    public static function adminCount(PDO $pdo): int
    {
        return (int) $pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn();
    }

    public static function seedAdmin(PDO $pdo): bool
    {
        if (self::adminCount($pdo) > 0) {
            return false;
        }

        $statement = $pdo->prepare(
            'INSERT INTO admins (username, password_hash) VALUES (:username, :password_hash)'
        );
        $statement->execute([
            ':username'      => DEFAULT_ADMIN_USERNAME,
            ':password_hash' => DEFAULT_ADMIN_PASSWORD_HASH,
        ]);

        return true;
    }

    public static function seedTrips(PDO $pdo): int
    {
        if ((int) $pdo->query('SELECT COUNT(*) FROM trips')->fetchColumn() > 0) {
            return 0;
        }

        $trips = [
            [
                'destination_name' => "Cox's Bazar",
                'country'          => 'Bangladesh',
                'description'      => 'The long coastline, changing light and an evening that felt larger than the frame. Endless sand, fishing boats and the sound of the Bay of Bengal.',
                'price_or_budget'  => 'BDT 8,500 / person',
                'travel_date'      => '2026-02-14',
                'image_path'       => 'images/cox-bazar/thumbnail.jpg',
            ],
            [
                'destination_name' => 'Teknaf',
                'country'          => 'Bangladesh',
                'description'      => 'Waterways, quiet horizons and the feeling of standing at the edge of the map. A slow southern journey where the river meets the sea.',
                'price_or_budget'  => 'BDT 6,200 / person',
                'travel_date'      => '2026-02-16',
                'image_path'       => 'images/teknaf/thumbnail.jpg',
            ],
            [
                'destination_name' => 'Bandarban',
                'country'          => 'Bangladesh',
                'description'      => 'Layered hills, shifting mist and paths that disappear into green silence. Mornings begin above the clouds here.',
                'price_or_budget'  => 'BDT 9,000 / person',
                'travel_date'      => '2026-01-09',
                'image_path'       => 'images/bandarban/thumbnail.jpg',
            ],
            [
                'destination_name' => 'Sylhet',
                'country'          => 'Bangladesh',
                'description'      => 'Tea gardens, soft rain and slow roads lined with deep green. A chapter written in leaf shade and river light.',
                'price_or_budget'  => 'BDT 7,400 / person',
                'travel_date'      => '2025-12-20',
                'image_path'       => 'images/sylhet/thumbnail.jpg',
            ],
            [
                'destination_name' => 'Sitakunda',
                'country'          => 'Bangladesh',
                'description'      => 'Forest trails, uneven ground and the hidden quiet of the hills. A short escape that felt much further away than it was.',
                'price_or_budget'  => 'BDT 3,800 / person',
                'travel_date'      => '2025-11-15',
                'image_path'       => 'images/sitakunda/thumbnail.jpg',
            ],
            [
                'destination_name' => 'Akhilpur',
                'country'          => 'Bangladesh',
                'description'      => 'A coastal chapter represented by the light and open shore of Patenga Beach. Nets, sea air and an evening tide.',
                'price_or_budget'  => 'BDT 2,500 / person',
                'travel_date'      => '2025-08-02',
                'image_path'       => 'images/akhilpur/patenga-beach.jpg',
            ],
            [
                'destination_name' => 'Guliakhali',
                'country'          => 'Bangladesh',
                'description'      => 'Open water, shifting grass and a quieter side of the coast. The kind of place where the light makes you stop without deciding to.',
                'price_or_budget'  => 'BDT 3,200 / person',
                'travel_date'      => '2025-07-19',
                'image_path'       => 'images/guliakhali/thumbnail.jpg',
            ],
        ];

        $statement = $pdo->prepare(
            'INSERT INTO trips
                (destination_name, country, description, price_or_budget, travel_date, image_path)
             VALUES
                (:destination_name, :country, :description, :price_or_budget, :travel_date, :image_path)'
        );

        $pdo->beginTransaction();

        try {
            foreach ($trips as $trip) {
                $statement->execute($trip);
            }

            $pdo->commit();
        } catch (Throwable $error) {
            $pdo->rollBack();
            throw $error;
        }

        return count($trips);
    }

    public static function seedGalleryPhotos(PDO $pdo): int
    {
        if ((int) $pdo->query('SELECT COUNT(*) FROM gallery_photos')->fetchColumn() > 0) {
            return 0;
        }

        $photos = [
            ["Cox's Bazar", 'The Long Shore', 'images/cox-bazar/photo1.jpg'],
            ["Cox's Bazar", 'Last Light', 'images/cox-bazar/photo2.jpg'],
            ["Cox's Bazar", 'Feet in the Sand', 'images/cox-bazar/photo3.jpg'],
            ["Cox's Bazar", 'Before Dark', 'images/cox-bazar/photo4.jpg'],
            ['Teknaf', 'The Water Road', 'images/teknaf/photo1.jpg'],
            ['Teknaf', 'River Light', 'images/teknaf/photo2.jpg'],
            ['Teknaf', 'Green Channels', 'images/teknaf/photo3.jpg'],
            ['Teknaf', 'Edge of Day', 'images/teknaf/photo4.jpg'],
            ['Bandarban', 'Into the Hills', 'images/bandarban/photo1.jpg'],
            ['Bandarban', 'Morning Mist', 'images/bandarban/photo2.jpg'],
            ['Bandarban', 'A Green Distance', 'images/bandarban/photo3.jpg'],
            ['Bandarban', 'The Climb', 'images/bandarban/photo4.jpg'],
            ['Sylhet', 'After Rain', 'images/sylhet/photo1.jpg'],
            ['Sylhet', 'Garden Path', 'images/sylhet/photo2.jpg'],
            ['Sylhet', 'Tree Tunnel', 'images/sylhet/photo3.jpg'],
            ['Sylhet', 'Open Green', 'images/sylhet/photo4.jpg'],
            ['Sitakunda', 'The Trail', 'images/sitakunda/photo1.jpg'],
            ['Sitakunda', 'Under the Canopy', 'images/sitakunda/photo2.jpg'],
            ['Sitakunda', 'Hidden Water', 'images/sitakunda/photo3.jpg'],
            ['Sitakunda', 'The Way Back', 'images/sitakunda/thumbnail.jpg'],
            ['Akhilpur', 'Patenga Shore', 'images/akhilpur/photo1.jpg'],
            ['Akhilpur', 'Nets at Sunset', 'images/akhilpur/photo2.jpg'],
            ['Akhilpur', 'Sea Air', 'images/akhilpur/photo3.jpg'],
            ['Akhilpur', 'After the Tide', 'images/akhilpur/photo4.jpg'],
            ['Guliakhali', 'Green Water', 'images/guliakhali/photo1.jpg'],
            ['Guliakhali', 'Shoreline Birds', 'images/guliakhali/photo2.jpg'],
            ['Guliakhali', 'Walking the Coast', 'images/guliakhali/photo3.jpg'],
            ['Guliakhali', 'Sea and Sky', 'images/guliakhali/photo4.jpg'],
        ];

        $statement = $pdo->prepare(
            'INSERT INTO gallery_photos (album_name, caption, image_path)
             VALUES (:album_name, :caption, :image_path)'
        );

        $pdo->beginTransaction();

        try {
            foreach ($photos as [$albumName, $caption, $imagePath]) {
                $statement->execute([
                    ':album_name' => $albumName,
                    ':caption'    => $caption,
                    ':image_path' => $imagePath,
                ]);
            }

            $pdo->commit();
        } catch (Throwable $error) {
            $pdo->rollBack();
            throw $error;
        }

        return count($photos);
    }

    /**
     * Generate a fresh random password (used by `setup.php --new-password`).
     * Ambiguous characters (O/0, l/1) and shell-hostile characters are avoided.
     */
    public static function randomPassword(int $length = 18): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#%&*?';
        $maxIndex = strlen($alphabet) - 1;
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $alphabet[random_int(0, $maxIndex)];
        }

        return $password;
    }
}
