--
-- Sql query template fetch hashtag posts
--

SELECT p.*,u.`avatar`,u.`username`,(SELECT COUNT(l.`id`) FROM  `{%t_likes%}` l WHERE l.`post_id` = p.`post_id` ) AS likes, (SELECT COUNT(cm.`id`) FROM `{%t_comm%}` cm WHERE cm.`post_id` = p.`post_id` ) AS votes FROM `{%t_posts%}` p
	
	INNER JOIN `{%t_users%}` u ON u.`user_id` = p.`user_id` AND u.`p_privacy` > '0'

	WHERE p.`user_id` NOT IN (SELECT b1.`profile_id` FROM `{%t_blocks%}` b1 WHERE b1.`user_id` = {%user_id%})

	AND p.`user_id` NOT IN (SELECT b2.`user_id` FROM `{%t_blocks%}` b2 WHERE b2.`profile_id` = {%user_id%})

	{%if offset%}
		AND p.`post_id` < {%offset%}
	{%endif%}

    {%if hashtag_id%}
		AND p.`description` LIKE CONCAT('%#[{%hashtag_id%}]%')
	{%endif%}

	ORDER BY RAND() LIMIT {%total_limit%}

