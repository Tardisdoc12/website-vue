<?php
defined('ABSPATH') || exit;

function monplugin_run_migrations() {
    global $wpdb;
    $table_events = $wpdb->prefix . "events";
    $table_inscribes = $wpdb->prefix . "inscrits";
    $table_source = $wpdb->prefix . "source";
    $table_conseils = $wpdb->prefix . "conseils";
    $table_favoris = $wpdb->prefix . "favoris";
    $table_medias = $wpdb->prefix . "medias";

    $column = $wpdb->get_results("SHOW COLUMNS FROM $table_events LIKE 'description'");
    if (!empty($column) && $column[0]->Type !== 'longtext') {
        $wpdb->query("ALTER TABLE $table_events MODIFY COLUMN description LONGTEXT");
    }

    // --- 1️⃣ Ajouter post_id si elle n'existe pas ---
    $column = $wpdb->get_results("SHOW COLUMNS FROM $table_events LIKE 'post_id'");
    if (empty($column)) {
        $wpdb->query("ALTER TABLE $table_events ADD post_id BIGINT(20) UNSIGNED NULL AFTER id");
    }

    $column = $wpdb->get_results("SHOW COLUMNS FROM $table_medias LIKE 'parent_id'");
    if (empty($column)) {
        $wpdb->query("ALTER TABLE $table_medias ADD parent_id VARCHAR(200) NULL AFTER file_type");
    }

    $column = $wpdb->get_results("SHOW COLUMNS FROM $table_events LIKE 'update_date'");
    if (empty($column)) {
        $wpdb->query("ALTER TABLE $table_events ADD update_date DATETIME NULL AFTER billeterie_url");
    }

    $column = $wpdb->get_results("SHOW COLUMNS FROM $table_inscribes LIKE 'status'");
    if (empty($column)) {
        $wpdb->query("ALTER TABLE $table_inscribes ADD status ENUM('inscrit', 'attente') NOT NULL DEFAULT 'inscrit' AFTER encadrement");
        // Tous les inscrits existants héritent de 'inscrit' grâce au DEFAULT
    }

    $column = $wpdb->get_results("SHOW COLUMNS FROM $table_inscribes LIKE 'date_inscrit'");
    if (empty($column)) {
        $wpdb->query("ALTER TABLE $table_inscribes ADD date_inscrit DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL AFTER status");
    }

    $column = $wpdb->get_results("SHOW COLUMNS FROM $table_events LIKE 'attente_places'");
    if (empty($column)) {
        $wpdb->query("ALTER TABLE $table_events ADD attente_places INT UNSIGNED NOT NULL DEFAULT 0 AFTER nonsubscribe_places");
    }

    $column = $wpdb->get_results("SHOW COLUMNS FROM $table_events LIKE 'billeterie_url'");
    if (empty($column)) {
        $wpdb->query("ALTER TABLE $table_events ADD billeterie_url VARCHAR(500) NULL AFTER closed_inscription");
    }

    // --- Ajouter closed_inscription si elle n'existe pas ---
    $column = $wpdb->get_results("SHOW COLUMNS FROM $table_events LIKE 'closed_inscription'");
    if (empty($column)) {
        $wpdb->query("ALTER TABLE $table_events ADD closed_inscription TINYINT(1) NOT NULL DEFAULT 0 AFTER nonsubscribe_places");
    }

    $column = $wpdb->get_results("SHOW COLUMNS FROM $table_inscribes LIKE 'encadrement'");
    if (empty($column)) {
        $wpdb->query("ALTER TABLE $table_inscribes ADD encadrement TINYINT(1) NOT NULL DEFAULT 0 AFTER goal");
    }

    $column2 = $wpdb->get_results("SHOW COLUMNS FROM $table_source LIKE 'id_wp'");
    if (empty($column2)) {
        $wpdb->query("ALTER TABLE $table_source ADD id_wp BIGINT(20) UNSIGNED NULL AFTER tag");
    }

    // 3️⃣ Vérifier index user_file
    $index = $wpdb->get_results("SHOW INDEX FROM $table_conseils WHERE Key_name = 'user_file'");
    if (empty($index)) {
        $wpdb->query("
            ALTER TABLE $table_conseils
            ADD KEY user_file (wp_user_id, file_id)
        ");
    }

    $index = $wpdb->get_results("SHOW INDEX FROM $table_favoris WHERE Key_name = 'user_file'");
    if (empty($index)) {
        $wpdb->query("
            ALTER TABLE $table_favoris
            ADD KEY user_file (wp_user_id, file_id)
        ");
    }

    $column = $wpdb->get_results("SHOW COLUMNS FROM $table_events LIKE 'billeterie_id'");
    if (empty($column)) {
        $wpdb->query("ALTER TABLE $table_events ADD billeterie_id BIGINT(20) UNSIGNED NULL AFTER billeterie_url");
    }

    $column = $wpdb->get_results("SHOW COLUMNS FROM $table_inscribes LIKE 'payement_status'");
    if (empty($column)) {
        $wpdb->query("ALTER TABLE $table_inscribes ADD payement_status ENUM('pending', 'completed', 'failed') NOT NULL DEFAULT 'pending' AFTER date_inscrit");
    }

    $column = $wpdb->get_results("SHOW COLUMNS FROM $table_inscribes LIKE 'champs_speciaux'");
    if (empty($column)) {
        $wpdb->query("ALTER TABLE $table_inscribes ADD COLUMN champs_speciaux JSON NULL AFTER goal");
    }

    $column_goal = $wpdb->get_results("SHOW COLUMNS FROM $table_inscribes LIKE 'goal'");
    if (!empty($column_goal)) {

        // 1. Récupérer tous les inscrits ayant une valeur dans 'goal'
        $inscrits_avec_goal = $wpdb->get_results(
            "SELECT id, goal, champs_speciaux FROM $table_inscribes WHERE goal IS NOT NULL AND goal != ''"
        );

        foreach ($inscrits_avec_goal as $inscrit) {
            $champs_speciaux = json_decode($inscrit->champs_speciaux, true) ?: [];
            $champs_speciaux['Objectif'] = $inscrit->goal;

            $wpdb->update(
                $table_inscribes,
                ['champs_speciaux' => wp_json_encode($champs_speciaux)],
                ['id' => $inscrit->id]
            );
        }

        // 2. Supprimer l'ancienne colonne
        $wpdb->query("ALTER TABLE $table_inscribes DROP COLUMN goal");
    }

    $column = $wpdb->get_results("SHOW COLUMNS FROM $table_events LIKE 'billeterie_url'");
    if (!empty($column)) {
        $wpdb->query("ALTER TABLE $table_events DROP COLUMN billeterie_url");
    }

    $column = $wpdb->get_results("SHOW COLUMNS FROM $table_events LIKE 'billeterie_id'");
    if (!empty($column)) {
        $wpdb->query("ALTER TABLE $table_events DROP COLUMN billeterie_id");
    }

    $column_adherent_price = $wpdb->get_results("SHOW COLUMNS FROM $table_events LIKE 'adherent_price'");
    if (empty($column_adherent_price)) {
        $wpdb->query("ALTER TABLE $table_events ADD adherent_price DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER closed_inscription");
    }

    $column_non_adherent_price = $wpdb->get_results("SHOW COLUMNS FROM $table_events LIKE 'non_adherent_price'");
    if (empty($column_non_adherent_price)) {
        $wpdb->query("ALTER TABLE $table_events ADD non_adherent_price DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER adherent_price");
    }

    $column_payement_title = $wpdb->get_results("SHOW COLUMNS FROM $table_events LIKE 'payement_title'");
    if (empty($column_payement_title)) {
        $wpdb->query("ALTER TABLE $table_events ADD payement_title VARCHAR(255) DEFAULT NULL AFTER non_adherent_price");
    }

    $table_billeterie = $wpdb->prefix . "billetteries";
    $wpdb->query("DROP TABLE IF EXISTS $table_billeterie");

    // --- 3️⃣ Flag pour éviter de relancer la migration ---
    update_option('monplugin_last_migration', time());
}