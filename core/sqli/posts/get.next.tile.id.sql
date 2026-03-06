SELECT `post_id`
FROM `{%table%}` as p
WHERE
    `post_id` <>
    AND p.`type` = 'tile'
    AND p.`type` != 'image'
    AND p.`type` != 'video'
ORDER BY RAND()
LIMIT 1;