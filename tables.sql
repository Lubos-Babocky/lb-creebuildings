CREATE TABLE `lb_creebuildings_project` (
	`id` int unsigned not null AUTO_INCREMENT,
	`post_id` int unsigned default 0 not null,
	`crdate` int unsigned default 0 not null,
	`tstamp` int unsigned default 0 not null,
	`access_type` varchar(255) default '' not null,
	`project_id` varchar(255) default '' not null,
	`title` varchar(255) default '' not null,
	`sub_title` varchar(255) default '' not null,
	`location` varchar(255) default '' not null,
	`client` varchar(255) default '' not null,
	`gross_floor_area` varchar(255) default '' not null,
	`project_start` varchar(255) default '' not null,
	`project_completion` varchar(255) default '' not null,
	`type_of_use` varchar(255) default '' not null,
	`project_stage` varchar(255) default '' not null,
	`processed` tinyint unsigned default 0 not null,
	`latitude` decimal(20,17) default 0.00000000000000000 not null,
	`longitude` decimal(20,17) default 0.00000000000000000 not null,
	PRIMARY KEY(`id`),
	UNIQUE (`project_id`)
);

CREATE TABLE `lb_creebuildings_project_image` (
	`uid` varchar(50) default '' not null,
	`project_id` varchar(20) default '' not null,
	`image_id` varchar(20) default '' not null,
	`type_id` varchar(20) default '' not null,
	`source_type` varchar(255) default '' not null,
	`file_type` varchar(20) default '' not null,
	`api_url` varchar(255) default '' not null,
        `public_url` varchar(255) default '' not null,
	`width` int unsigned default 0 not null,
	`height` int unsigned default 0 not null,
	`image_post_id` int unsigned default 0 not null,
	`processed` tinyint unsigned default 0 not null,
	UNIQUE (`uid`)
);

CREATE TABLE `lb_creebuildings_project_image_copy` (
	`uid` varchar(50) default '' not null,
	`crdate` int unsigned default 0 not null,
	`tstamp` int unsigned default 0 not null,
	`project_id` varchar(255) default '' not null,
	`image_id` varchar(255) default '' not null,
	`url` varchar(255) default '' not null,
        `image_src` varchar(255) default '' not null,
	`title` varchar(255) default '' not null,
	`file_name` varchar(255) default '' not null,
	`source_type` varchar(255) default '' not null,
	`image_post_id` int unsigned default 0 not null,
	`processed` tinyint unsigned default 0 not null,
	`storage_path` varchar(255) default '' not null,
	UNIQUE (`uid`)
);

CREATE TABLE `lb_creebuildings_project_property` (
	`uid` varchar(50) default '' not null,
	`crdate` int unsigned default 0 not null,
	`tstamp` int unsigned default 0 not null,
	`group_id` varchar(50) default '' not null,
	`group_name` varchar(50) default '' not null,
	`property_id` varchar(50) default '' not null,
	`property_name` varchar(50) default '' not null,
	`wp_meta_key` varchar(50) default '' not null,
	`acf_id` varchar(50) default '' not null,
	UNIQUE (`uid`)
);

CREATE TABLE `lb_creebuildings_project_property_mm` (
	`uid` varchar(100) default '' not null,
	`project_id` varchar(50) default '' not null,
	`property_id` varchar(50) default '' not null,
	`property_value` varchar(50) default '' not null,
	UNIQUE (`uid`)
);

CREATE TABLE `lb_creebuildings_project_participant` (
	`uid` varchar(50) default '' not null,
	`participant_id` varchar(50) default '' not null,
	`participant_name` varchar(50) default '' not null,
	`project_id` varchar(50) default '' not null,
	`is_main` tinyint default 0 not null,
	`role_key` varchar(5) default '' not null,
	`role_title` varchar(50) default '' not null,
	UNIQUE (`uid`)
);

CREATE TABLE `lb_creebuildings_project_participant_role` (
	`key` varchar(2) default '' not null,
	`title` varchar(50) default '' not null,
	UNIQUE (`key`)
);

CREATE TABLE `lb_creebuildings_partner` (
	`id` int unsigned not null AUTO_INCREMENT,
	`post_id` int unsigned default 0 not null,
	`crdate` int unsigned default 0 not null,
	`tstamp` int unsigned default 0 not null,
	`title` varchar(255) default '' not null,
	`subtitle` varchar(255) default '' not null,
	`partner_id` varchar(255) default '' not null,
	`latitude` decimal(20,17) default 0.00000000000000000 not null,
	`longitude` decimal(20,17) default 0.00000000000000000 not null,
	`address_street_1` varchar(255) default '' not null,
	`address_street_2` varchar(255) default '' not null,
	`address_city` varchar(255) default '' not null,
	`address_state` varchar(255) default '' not null,
	`address_zip` varchar(255) default '' not null,
	`address_country_name` varchar(255) default '' not null,
	`address_country_code` varchar(255) default '' not null,
	`avatar_api_url` varchar(255) default '' not null,
	`avatar_storage_path` varchar(255) default '' not null,
	`avatar_post_id` varchar(255) default '' not null,
	`avatar_image_processed` tinyint unsigned default 0 not null,
	PRIMARY KEY(`id`),
	UNIQUE (`partner_id`)
);