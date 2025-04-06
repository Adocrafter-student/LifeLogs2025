INSERT INTO blogs
  (user_id, title, summary, content, image_url, caption, category, tag, likes, dislikes, created_at)
VALUES
  -- 1) John Doe -> user_id = 1
  (
    1,
    'How running 10 miles a day changed my life forever',
    'When addiction got a better of me, I have chosen a lifestyle which changed me forever.',
    'When addiction got the best of me, I chose a lifestyle that transformed everything...',
    'frontend/static-assets/images/running-guy.jpg',
    'Running can change your life.',
    'featured',
    'lifestyle',
    12, 2,
    '2024-01-01 00:00:00'
  ),
  
  -- 2) Jennifer Farrah -> user_id = 2
  (
    2,
    'Quick peek inside of my little garden',
    'Who does not like to relax with the scenery of fresh vegetables you cared for.',
    'Who doesn''t like relaxing surrounded by the greenery of self-grown vegetables...',
    'frontend/static-assets/images/gardening.jpg',
    'My peaceful green retreat.',
    'featured',
    'home',
    12, 2,
    '2023-12-11 00:00:00'
  ),
  
  -- 3) Anonymous -> user_id = 3
  (
    3,
    'How I beat David Goggins by eating cereal',
    'Nobody believed me until I showed the results of eating better.',
    'Nobody believed it until I showed them the truth about cereal power...',
    'frontend/static-assets/images/david-goggings.jpg',
    'Yes, cereal really helped.',
    'featured',
    'funny',
    12, 2,
    '2024-02-01 00:00:00'
  ),
  
  -- 4) GamerX -> user_id = 4
  (
    4,
    'How I won by playing the objective',
    'Step 1, keep your mind clear and focus.',
    'Step 1, keep your mind clear and focus...',
    'frontend/static-assets/images/objective.jpg',
    'Focus wins games.',
    'latest',
    'gaming',
    12, 2,
    '2024-02-05 00:00:00'
  ),
  
  -- 5) Chef Gordon -> user_id = 5
  (
    5,
    'This cooking recipe changed my life',
    'With a small amount of this secret sauce, anything is possible.',
    'With a small amount of this secret sauce, anything is possible...',
    'frontend/static-assets/images/gordon.jpg',
    'The sauce makes the dish.',
    'latest',
    'cooking',
    12, 2,
    '2024-02-08 00:00:00'
  ),
  
  -- 6) Yami -> user_id = 6
  (
    6,
    'How this Yu-Gi-Oh deck boosted my wins',
    'My win percentage doubled after getting those cards.',
    'My win percentage doubled after using these cards...',
    'frontend/static-assets/images/yugio.jpg',
    'Believe in the heart of the cards.',
    'latest',
    'gaming',
    12, 2,
    '2024-02-10 00:00:00'
  );
