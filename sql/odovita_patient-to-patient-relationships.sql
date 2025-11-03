
CREATE TABLE `patient_relationships` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uuid` binary(16) DEFAULT NULL,
  `patient_id` bigint(20) NOT NULL COMMENT 'References patient_data.id',
  `related_patient_id` bigint(20) NOT NULL COMMENT 'References patient_data.id',
  `relationship_type` varchar(50) NOT NULL COMMENT 'Maps to list_options',
  `notes` text,
  `created_by` int(11) NOT NULL COMMENT 'References users.id',
  `created_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  `active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`),
  UNIQUE KEY `unique_relationship` (`patient_id`, `related_patient_id`, `relationship_type`, `active`),
  KEY `patient_id` (`patient_id`),
  KEY `related_patient_id` (`related_patient_id`),
  KEY `relationship_type` (`relationship_type`),
  FOREIGN KEY (`patient_id`) REFERENCES `patient_data` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`related_patient_id`) REFERENCES `patient_data` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`) VALUES ('patient_relationship_types', 'family_member', 'Family Member', 30, 0, 0, '', '', '', 0, 0, 1, '', 1);

INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`, `edit_options`) VALUES ('patient_relationship_types', 'household_member', 'Household Member', 50, 0, 0, '', '', '', 0, 0, 1, '', 1);