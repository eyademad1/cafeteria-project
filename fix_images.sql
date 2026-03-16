-- Fix Hot Chocolate and Cheesecake images with better Unsplash URLs

-- Hot Chocolate (id=25)
UPDATE `products` SET `image` = 'https://images.unsplash.com/photo-1544787219-7f47ccb76574?w=400&q=80' WHERE `id` = 25;

-- Cheesecake (id=19)
UPDATE `products` SET `image` = 'https://images.unsplash.com/photo-1533134242443-d4fd215305ad?w=400&q=80' WHERE `id` = 19;