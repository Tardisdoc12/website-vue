<?php
//--------------------------------------------------------------------------------------------------
/*
* FILENAME: inscription.php
* AUTHOR: Jean Anquetil
* DATE: 2026-09-19
* DESCRIPTIOn : 
*/
//--------------------------------------------------------------------------------------------------
// Imports

if (!defined('ABSPATH')) exit;



//--------------------------------------------------------------------------------------------------
// Functions

function mps_tools_migration_inscription_table() {
    global $wpdb;
    $table_inscrits = $wpdb->prefix . "inscrits";
    
    $column = $wpdb->get_results("SHOW COLUMNS FROM $table_inscrits LIKE 'status'");
    if (empty($column)) {
        $wpdb->query("ALTER TABLE $table_inscrits ADD status ENUM('inscrit', 'attente') NOT NULL DEFAULT 'inscrit' AFTER encadrement");
        // Tous les inscrits existants héritent de 'inscrit' grâce au DEFAULT
    }

    $column = $wpdb->get_results("SHOW COLUMNS FROM $table_inscrits LIKE 'date_inscrit'");
    if (empty($column)) {
        $wpdb->query("ALTER TABLE $table_inscrits ADD date_inscrit DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL AFTER status");
    }

    $column = $wpdb->get_results("SHOW COLUMNS FROM $table_inscrits LIKE 'encadrement'");
    if (empty($column)) {
        $wpdb->query("ALTER TABLE $table_inscrits ADD encadrement TINYINT(1) NOT NULL DEFAULT 0 AFTER goal");
    }

    $column = $wpdb->get_results("SHOW COLUMNS FROM $table_inscrits LIKE 'payement_status'");
    if (empty($column)) {
        $wpdb->query("ALTER TABLE $table_inscrits ADD payement_status ENUM('pending', 'completed', 'failed') NOT NULL DEFAULT 'pending' AFTER date_inscrit");
    }

    $column = $wpdb->get_results("SHOW COLUMNS FROM $table_inscrits LIKE 'champs_speciaux'");
    if (empty($column)) {
        $wpdb->query("ALTER TABLE $table_inscrits ADD COLUMN champs_speciaux JSON NULL AFTER goal");
    }

    $column_goal = $wpdb->get_results("SHOW COLUMNS FROM $table_inscrits LIKE 'goal'");
    if (!empty($column_goal)) {

        // 1. Récupérer tous les inscrits ayant une valeur dans 'goal'
        $inscrits_avec_goal = $wpdb->get_results(
            "SELECT id, goal, champs_speciaux FROM $table_inscrits WHERE goal IS NOT NULL AND goal != ''"
        );

        foreach ($inscrits_avec_goal as $inscrit) {
            $champs_speciaux = json_decode($inscrit->champs_speciaux, true) ?: [];
            $champs_speciaux['Objectif'] = $inscrit->goal;

            $wpdb->update(
                $table_inscrits,
                ['champs_speciaux' => wp_json_encode($champs_speciaux)],
                ['id' => $inscrit->id]
            );
        }

        // 2. Supprimer l'ancienne colonne
        $wpdb->query("ALTER TABLE $table_inscrits DROP COLUMN goal");
    }

    $column = $wpdb->get_results("SHOW COLUMNS FROM $table_inscrits LIKE 'payement_status'");

    if (!empty($column)) {
        $current_type = $column[0]->Type; // ex: "enum('pending','completed','failed')"

        if (strpos($current_type, "'cash'") === false) {
            $wpdb->query("ALTER TABLE $table_inscrits MODIFY payement_status ENUM('pending', 'completed', 'failed', 'cash') NOT NULL DEFAULT 'pending'");
        }
    }
}

//--------------------------------------------------------------------------------------------------
// End of file
//--------------------------------------------------------------------------------------------------