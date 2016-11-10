<?php
/*
 *
 * OGP - Open Game Panel
 * Copyright (C) Copyright (C) 2008 - 2012 The OGP Development Team
 *
 * http://www.opengamepanel.org/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 */

// Shop Settings
define('currency', "Devise");
define('available_invoice_types', "Types de facturation disponibles");
define('hourly', "Par heure");
define('monthly', "Par mois");
define('annually', "Par an");
define('tax_amount', "Montant de la taxe");
define('paypal_email', "E-mail Paypal");
define('update_settings', "Mise � jour des param�tres");
define('settings_updated', "Param�tres mis � jour.");
define('payment_gateway', "Moyen de paiement");
define('paygol_service', "PayGol");
define('paygol_service_id', "PayGol Service ID");
define('paypal', "PayPal");
define('paygol', "PayGol");
define('skrill', "Skrill");
define('skrill_merchant_info', "Skrill merchant information");
define('skrill_merchant_id', "Merchant ID.");
define('skrill_email', "Skrill Email");
define('skrill_secret_word', "Secret word");
define('skrill_secret_word_info', "The password must be set from your Skrill merchant account,<br>
									at <b>Settings</b>, click on <b>Developer Settings</b>,<br>
									If this option is not listed send an email to <i>merchantservices@skrill.com</i>.");
define('hash_stored_correctly', "Hash stored correctly");
define('currency_not_available_at', "The currency \"%s\" is not available at %s.");
define('robokassa', "Robokassa");
define('robokassa_service', "Robokassa service");
define('robokassa_merchant_login', "Merchant Login");
define('robokassa_securepass1', "Secure Password 1");
define('robokassa_securepass2', "Secure Password 2");

// Shop
define('your_cart', "Votre panier");
define('starting_on', "D�s");
define('slots', "slots");
define('hour', "Heure");
define('month', "Mois");
define('year', "Ann�e");
define('hours', "Heures");
define('months', "Mois");
define('years', "Ann�es");
define('service_name', "Nom du service");
define('rcon_pass', "Mot de passe RCON");
define('ftp_pass', "Mot de passe FTP");
define('available_ips', "IPs disponibles");
define('max_players', "Joueurs maximums");
define('invoice_duration', "Dur�e");
define('calculate_price', "Calculer le prix");
define('buy', "ACHETER");
define('back_to_list', "Retourner � la liste");
define('ip', "IP");
define('subtotal', "Sous-total");
define('rate', "Taux");
define('total', "Total");
define('save', "Sauvegarder");
define('you_need_to', "Vous devez vous");
define('register', "enregistrer");
define('and', "et vous");
define('log_in', "connecter");
define('to_purchase_a_service', "pour acheter un service");
define('available_services', "Services disponibles");
define('payment_is_pending_of_approval', "The payment is pending of approval.");
define('back_to_your_cart', "Go back to your cart.");
define('expired', "Expired");
define('removed', "Removed");
define('extended', "Extended");
define('installation_and_expiration_date', "Installation and expiration date");
define('expiration_date', "Expiration date");
define('removal_date', "Removal date");
define('installation_date', "Installation date");
define('enable_server', "Enable server");
define('success', "SUCCESS");
define('redirecting_to_game_monitor', "Redirecting to Game Monitor...");
define('starting_installations', "Starting installations...");

// Orders
define('order_id', "Commande ID");
define('home_name', "Nom du service");
define('tax', "Taxe");
define('pay_from', "Payer � partir de %s");
define('set_as_paid', "D�finir comme pay�");
define('create_server', "Cr�er le serveur");
define('see_invoice', "Voir la facture");
define('paid', "Pay�");
define('not_paid', "Non pay�");
define('procesing_payment', "Paiement en cours");
define('paid_and_installed', "Pay� et install�");
define('add_to_cart', "Ajouet au panier");
define('cart_id', "ID du panier");
define('order_desc', "Description de la commande");
define('remove_from_cart', "Supprimer du panier");
define('remove_cart', "Supprimer le panier");
define('there_are_no_orders_in_cart', "Il n'y a pas de commande dans ce panier.");
define('redirecting_to', "Redirection vers %s...");

// Services
define('id', "ID#");
define('remote_server', "Serveur distant");
define('price_hourly', "Prix / heure");
define('price_monthly', "Prix / mois");
define('price_year', "Prix / ann�e");
define('service_image_url', "URL vers image du service");
define('remove_service', "Supprimer le service");
define('add_service', "Ajouter un service");
define('current_services', "Services existants");
define('max_slot_qty', "Quantit� de slots max.");
define('min_slot_qty', "Quantit� de slots min.");
define('ftp_account', "Compte FTP");
define('select_install_method', "M�thode d'installation");
define('url_for_manual_install', "URL pour installation manuelle");
define('description', "Description");
define('image_url', "URL de l'image");
define('access_rights', "Droits d'acc�s");
define('allow_update', "Autoriser la mise � jour");
define('allow_file_management', "Autoriser la gestion des fichiers");
define('allow_parameter_usage', "Autoriser les param�tres");
define('allow_extra_parameters_usage', "Autoriser les param�tres personnalis�s");
define('allow_ftp_usage', "Autoriser le FTP");
define('allow_custom_fields', "Allow Custom Fields");
define('enabled', "Activ�");
define('disabled', "D�sactiv�");
define('steam', "Steam");
define('rsync', "Rsync");
define('manual_from_url', "Manuelle depuis une URL");

// View Invoice
define('business', "Business");
define('business_email', "E-mail business");
define('game_server_order', "Commande serveur de jeux");
define('item', "Objet");
define('slot_cost', "Co�t du slot");
define('slot_quantity', "Quantit� de slot");
define('order_price', "Prix de la commande");
define('order', "Commande");
define('date', "Date");
define('price', "Prix");
define('invoice', "Facture");
define('print_invoice', "Imprimer la facture");
define('extend', "Etendre");
?>
