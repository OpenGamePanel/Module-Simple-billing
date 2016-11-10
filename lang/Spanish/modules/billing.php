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

define('currency', "Moneda");
define('available_invoice_types', "Tipo de facturación");
define('hourly', "Por horas");
define('monthly', "Mensual");
define('annually', "Anual");
define('tax_amount', "Cantidad de IVA");
define('paypal_email', "Email de PayPal");
define('update_settings', "Actualizar configuración");
define('settings_updated', "Configuración actualizada.");
define('payment_gateway', "Pasarela de pago");
define('paygol_service', "Servicio PayGol");
define('paygol_service_id', "ID de PayGol");
define('paypal', "PayPal");
define('paygol', "PayGol");
define('skrill', "Skrill");
define('skrill_merchant_info', "Información de cuenta Skrill");
define('skrill_merchant_id', "ID de Negocio.");
define('skrill_email', "Email de Skrill");
define('skrill_secret_word', "Palabra secreta");
define('skrill_secret_word_info', "La palabra secreta se configura desde su cuenta de negocio de Skrill,<br>
									en <b>Ajustes</b> pulse sobre <b>Ajustes del desarrollador</b>,<br>
									si esta opción no aparece envie un email a <i>merchantservices@skrill.com</i>.");
define('hash_stored_correctly', "Hash guardado correctamente");
define('currency_not_available_at', "La divisa \"%s\" no esta disponible en %s.");
define('robokassa', "Robokassa");
define('robokassa_service', "Servicio Robokassa");
define('robokassa_merchant_login', "Ususrio Robokassa");
define('robokassa_securepass1', "Contraseña Segura 1");
define('robokassa_securepass2', "Contraseña Segura 2");

// Shop
define('your_cart', "Su Carro");
define('starting_on', "Desde");
define('slots', "Slots");
define('hour', "Hora");
define('month', "Mes");
define('year', "Año");
define('hours', "Horas");
define('months', "Meses");
define('years', "Años");
define('service_name', "Nombre del servicio");
define('rcon_pass', "Contraseña RCON");
define('ftp_pass', "Contraseña FTP");
define('available_ips', "IP disponibles");
define('max_players', "Max Jugadores");
define('invoice_duration', "Duración");
define('calculate_price', "Calcular Precio");
define('buy', "Comprar");
define('back_to_list', "Volver a la lista");
define('ip', "IP");
define('subtotal', "Subtotal");
define('rate', "Impuesto");
define('total', "Total");
define('save', "Guardar");
define('you_need_to', "Necesitas");
define('register', "Registrarte");
define('log_in', "Loguearte");
define('to_purchase_a_service', "para comprar un servicio");
define('available_services', "Servicios disponibles");
define('payment_is_pending_of_approval', "El pago está pendiente de aprovacion.");
define('back_to_your_cart', "Vuelve a tu carro.");
define('expired', "Caducado");
define('removed', "Eliminado");
define('extended', "Extendido");
define('installation_and_expiration_date', "Fecha de instalación y caducidad");
define('expiration_date', "Fecha de caducidad");
define('removal_date', "Fecha de eliminación");
define('installation_date', "Fecha de instalación");
define('enable_server', "Reactivar servicio");
define('success', "COMPLETADO");
define('redirecting_to_game_monitor', "Redireccionando al monitor...");
define('starting_installations', "Iniciando instalaciones...");

// Orders

define('order_id', "ID de pedido");
define('home_name', "Nombre Principal");
define('tax', "Impuesto");
define('pay_from', "Pagar desde %s");
define('set_as_paid', "Marcar como pagado");
define('create_server', "Crear el Servidor");
define('see_invoice', "Ver Factura");
define('paid', "Pagado");
define('not_paid', "No Pagado");
define('procesing_payment', "Procesando Pago");
define('paid_and_installed', "Pagado e instalado");
define('add_to_cart', "Añadir Al Carro");
define('cart_id', "ID De carro");
define('order_desc', "Descripcion Del Pedido");
define('remove_from_cart', "Quitar Del Carro");
define('remove_cart', "Eliminar El Carro");
define('there_are_no_orders_in_cart', "No hay ningún pedido en su carro.");
define('redirecting_to', "Redirigiendo a %s...");

// Services
define('id', "ID#");
define('remote_server', "Servidor Remoto");
define('price_hourly', "Precio por hora");
define('price_monthly', "Precio mensual");
define('price_year', "Precio anual");
define('service_image_url', "URL de la imagen");
define('remove_service', "Eliminar Servicio");
define('add_service', "Añadir Servicio");
define('current_services', "Servicios Actuales");
define('max_slot_qty', "Cantidad Maxima de Slots");
define('min_slot_qty', "Cantidad Minima de Slots");
define('ftp_account', "Cuenta FTP");
define('select_install_method', "Metodo de instalación");
define('url_for_manual_install', "URL para instalación manual");
define('description', "Descripción");
define('image_url', "URL de la imagen");
define('access_rights', "Derechos de acceso");
define('allow_update', "Habilitar Actualizaciones");
define('allow_file_management', "Habilitar Edicion de archivos");
define('allow_parameter_usage', "Habilitar Parametros");
define('allow_extra_parameters_usage', "Habilitar Parametros Extra");
define('allow_ftp_usage', "Habilitar FTP");
define('allow_custom_fields', "Habilitar Campos Personalizados");
define('enabled', "Activado");
define('disabled', "Desactivado");
define('steam', "Steam");
define('rsync', "Rsync");
define('manual_from_url', "Manual desde URL");

// View Invoice
define('business', "Negocio");
define('business_email', "Email del Negocio");
define('game_server_order', "Pedido De Servidor De Juegos");
define('item', "Objeto");
define('slot_cost', "Coste por Slot");
define('slot_quantity', "Cantidad de Slots");
define('order_price', "Precio del Pedido");
define('order', "Pedido");
define('date', "Fecha");
define('price', "Precio");
define('invoice', "Factura");
define('print_invoice', "Imprimir Factura");
define('extend', "Extender");
define('and', "y");

?>