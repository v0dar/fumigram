 SELECT * , (SELECT COUNT(`id`) FROM `{%t_conn%}` WHERE `following_id` = `user_id`) AS `followers` FROM `{%t_users%}`

	WHERE 
		`admin` = 0

	{%if offset%}
	AND 
		`user_id` < {%offset%}
	{%endif%}

	ORDER BY RAND()

	LIMIT {%total_limit%}