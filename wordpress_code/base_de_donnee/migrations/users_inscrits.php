<?php
//--------------------------------------------------------------------------------------------------
/*
* FILENAME: users_inscrits.php
* AUTHOR: Jean Anquetil
* DATE: 2026-09-19
* DESCRIPTIOn : 
*/
//--------------------------------------------------------------------------------------------------
// Imports

if (!defined('ABSPATH')) exit;



//--------------------------------------------------------------------------------------------------
// Functions
//--------------------------------------------------------------------------------------------------

function mps_tools_migration_users_inscrits_table() {
    global $wpdb;
    $table_users_inscrits = $wpdb->prefix . "users_inscrits";
    $table_inscrits        = $wpdb->prefix . "inscrits";

    $column = $wpdb->get_results("SHOW COLUMNS FROM $table_users_inscrits LIKE 'experience'");

    if (!empty($column)) {
        $inscrits_avec_experience = $wpdb->get_results(
            "SELECT id, experience FROM $table_users_inscrits WHERE experience IS NOT NULL AND experience != ''"
        );

        foreach ($inscrits_avec_experience as $user) {
            // Un utilisateur peut avoir plusieurs inscriptions (plusieurs événements)
            $inscriptions = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT id, champs_speciaux FROM $table_inscrits WHERE user_id = %d",
                    $user->id
                )
            );

            foreach ($inscriptions as $inscription) {
                $champs_speciaux = json_decode($inscription->champs_speciaux, true);
                if (!is_array($champs_speciaux)) {
                    $champs_speciaux = [];
                }

                $champs_speciaux['Experience à moto'] = $user->experience;

                $wpdb->update(
                    $table_inscrits,
                    ['champs_speciaux' => wp_json_encode($champs_speciaux)],
                    ['id' => $inscription->id]
                );
            }
        }

        $wpdb->query("ALTER TABLE $table_users_inscrits DROP COLUMN experience");

        if ($wpdb->last_error) {
            error_log("MPS Tools : erreur lors du DROP COLUMN experience : " . $wpdb->last_error);
        }
    }
}

//--------------------------------------------------------------------------------------------------
// End of file
//--------------------------------------------------------------------------------------------------