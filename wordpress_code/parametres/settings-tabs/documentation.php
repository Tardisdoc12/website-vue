<?php

/**
 * Documentation tab pour utiliser le plugin.
 */
if (!defined('ABSPATH')) exit;

return [
    'label' => 'Documentation',
    'fields' => [
        '_heading_documentation' => [
            'type' => 'heading',
            'label' => 'Introduction',
        ],

        '_paragraph_documentation' => [
            'type' => 'html_text',
            'text' => '
                <p style="margin-left: 10px;">Ce plugin permet d\'ajouter divers outils et fonctionnalités pour tout type d\'association moto.</p>
                <p>Cependant, il est important de configurer correctement les options du plugin pour qu\'il fonctionne de manière optimale.
                <br>
                Chaque onglet renvoie à une fonctionnalité. Elle peut ou ne peut pas être pertinente selon les besoins de l\'association.
                <br>
                Assurez-vous de lire attentivement la documentation pour comprendre comment chaque fonctionnalité peut être utilisée efficacement.
                <br>
                Bonne lecture !
                <br>
                PS: N\'hésitez pas à consulter les autres onglets pour découvrir toutes les fonctionnalités disponibles.</p>
            ',
        ],

        '_heading_shortcodes' => [
            'type' => 'heading',
            'label' => 'Shortcodes',
        ],

        '_paragraph_shortcodes' => [
            'type' => 'html_text',
            'text' => '<p>
                Les shortcodes permettent d\'insérer facilement certaines fonctionnalités du plugin dans vos pages ou articles.
                Dans une page ou un article veuillez utiliser l\'élément shortcode (ou code court en français).
                Vous pouvez ensuite insérer la balise suivante selon la fonctionnalité souhaitée:
                <br>
                <code>[calendar]</code> pour afficher un calendrier d\'événements et en créer de nouveaux 
                seulement pour les utilisateurs autorisés (voir la section "Permissions" pour plus de détails).
                <br>
                <code>[account]</code> pour afficher la création/connexion du compte utilisateur et la gestion du profil.
                <br>
                <code>[render_payement]</code> dans une page pour que l\'utilisateur est un visuel de si son paiement
                 a été effectué correctement ou non.
                <br>
                <code>[reinitialisation]</code> 
                dans une page pour que l\'utilisateur puisse réinitialiser son mot de passe  en cas d\'oubli.
                <br>
                <code>[event-page]</code> dans une page pour permettre l\'affichage d\'une page dédiée à un événement spécifique.
            </p>',
        ],

        '_heading_permissions' => [
            'type' => 'heading',
            'label' => 'Permissions des utilisateurs',
        ],

        '_paragraph_permissions' => [
            'type' => 'html_text',
            'text' => '<p>

                Les permissions des utilisateurs permettent de contrôler l\'accès à certaines fonctionnalités du plugin. <br>
                Il est important de créer ces rôles avec la bonne orthographe et les bonnes permissions attribuées dans wordpress.<br>
                Par exemple, un rôle mal orthographié ou avec des permissions incorrectes pourrait empêcher les utilisateurs d\'accéder aux fonctionnalités prévues.
                <br>
                Voici les rôles et permissions recommandés pour le bon fonctionnement du plugin et en sécurité:
                <br>
                <code>administrator</code> : (déjà présent dans WordPress) accès complet à toutes les fonctionnalités du plugin.
                <br>
                <code>bureau</code> : est réservé aux membres de la gestion de l\'association. Veillez à attribuer les permissions que vous souhaiter dans wordpress.
                Pour le plugin, ce rôle permet d\'accéder à toutes les fonctionnalités de gestion de l\'association sans avoir les droits d\'administration complets.
                <br>
                <code>encadrant</code> : est réservé aux intervenants ou encadrants de l\'association. Ce rôle permet d\'avoir accès aux informations des inscrits sans pouvoir les modifier.
                Pour les droits wordpress, ce rôle doit avoir juste les permissions de lecteur.
                <br>
                <code>adherent</code> : est réservé aux personnes ayant payer leur cotisation à l\'association. Ce rôle permet d\'accéder aux fonctionnalités réservées aux membres actifs.
                Pour les droits wordpress, ce rôle doit avoir juste les permissions de lecteur.
                <br>
                <code>non_adherent</code> : reservé aux personnes qui n\'ont pas payé leur cotisation à l\'association. Ce rôle n\'a que peu de droit.
                Pour les droits wordpress, ce rôle doit avoir juste les permissions de lecteur.
            </p>',
        ],

        '_heading_fonctionnalites' => [
            'type'=> 'heading',
            'label' => 'Fonctionnalités',
        ],

        '_paragraph_fonctionnalites' => [
            'type' => 'html_text',
            'text' => '<p>
                On va détailler ici les principales fonctionnalités et comment les mettre en oeuvre.
                <br>
                <br>
                <div style="font-weight: bold;font-size: 14px;text-decoration: underline;">
                    Paiement avec helloAsso
                </div>
                Pour que vos utilisateurs puissent payer lors d\'inscription au événement ou de payer leur cotisation, il faut configurer le plugin dans l\'onglet "helloAsso" des paramètres.
                <br>
                Il vous faut, de helloAsso, les informations d\'API suivantes : 
                <ul>
                    <li>- Clé API</li>
                    <li>- Secret API</li>
                    <li>- Slug de l\'association</li>
                    <li>- URL de redirection</li>
                </ul>
                La clé API et le secret API sont trouvables dans votre compte helloAsso, dans la section "Intégration et API" de vos paramètres.<br>
                Le slug de l\'association est trouvable dans l\'URL de votre page helloAsso. (par exemple, l\'association MonAssociation aura pour slug "mon-association").<br>
                L\'URL de redirection est l\'URL vers laquelle vos utilisateurs seront redirigés après avoir effectué le paiement.
                <p style="font-weight: bold;"> Il est important de la configurer correctement.</p>
                Il faut que ce soit l\'URL de la page sur laquelle vous avez mis le shortcode <code>[render_payment]</code>.
                <br>
                <p style="font-weight: bold;">Point d\'attention :</p>
                <br>
                vous pouvez configurer la possibilité de laisser vos utilisateurs payer en liquidité. Dans ce cas, toutes inscriptions ayant cocher cette possibilité sera accépté dans la limite des places de l\'événement.
                Dans le cas d\'un paiement en liquidité, cela sera visible dans la liste des personnes inscrites à l\'événement.
                <br>
                <br>
                Si un paiement est effectué en ligne via le plugin helloAsso, la validation et la confirmation ainsi que la mise à jour de la liste des participants se feront automatiquement.
                il en va de même pour les cotisations.
            </p>',
        ],

        '_paragraph_kdrive' => [
            'type' => 'html_text',
            'text' => '<p>
                <div style="font-weight: bold;font-size: 14px;text-decoration: underline;">
                    Partage de média avec Kdrive
                </div>
                Si vous souhaitez que vos utilisateurs puissent partager et accéder à des images/vidéos via Kdrive, il est possible de l\'intégrer directement dans le plugin.
                <br>
                Il suffit de récupérer 3 éléments :
                <ul>
                    <li>- Le token</li>
                    <li>- le kdrive ID</li>
                    <li>- L\'ID du dossier racine</li>
                </ul>
                Le kdrive ID et l\'ID du dossier racine sont récupérables via l\'URL de votre espace Kdrive lorsque vous êtes dans le dossier racine.
                <br>
                Le token est généré dans les paramètres de votre compte Kdrive, dans la section "développeur" puis dans "Token API".
                <br>
                Lors de la connexion à votre compte sur votre site via le plugin, vous verrez une interface "Media" qui permettra de naviguer dans ce dossier.
            </p>',
        ],
    ],
];