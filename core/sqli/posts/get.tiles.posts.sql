--
-- Sql query template fetch tile posts
--

SELECT p.*,u.`avatar`,u.`username`,(SELECT COUNT(l.`id`) FROM  `{%t_likes%}` l WHERE l.`post_id` = p.`post_id` ) AS likes, (SELECT COUNT(cm.`id`) FROM  `{%t_comm%}` cm WHERE cm.`post_id` = p.`post_id` ) AS votes  FROM `{%t_posts%}` p
	
	INNER JOIN `{%t_users%}` u ON u.`user_id` = p.`user_id` AND u.`p_privacy` > '0'
    
    WHERE 
		`type` = 'tile'
    AND 
        p.`type` != 'image'
	AND 
        p.`user_id` NOT IN (SELECT b1.`profile_id` FROM `{%t_blocks%}` b1 WHERE b1.`user_id` = {%user_id%})
	AND 
        p.`user_id` NOT IN (SELECT b2.`user_id` FROM `{%t_blocks%}` b2 WHERE b2.`profile_id` = {%user_id%})

	{%if offset%}
		AND p.`post_id` < {%offset%}
	{%endif%}

	ORDER BY p.`post_id` DESC LIMIT {%total_limit%}