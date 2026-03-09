<?php
/**
 * Plugin Name: CF7 Sync
 * Description: Sync Contact Form 7 forms from versioned JSON manifests via WP-CLI.
 * Version: 0.1.0
 * Plugin URI: https://github.com/michelediss/the-logical-theme
 * Author: Michele Paolino
 * Author URI: https://michelepaolino.com
 * Text Domain: cf7-sync
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CF7_Sync_Plugin' ) ) {
	final class CF7_Sync_Plugin {
		const VERSION           = '0.1.0';
		const FORM_META_SLUG    = '_cf7_sync_slug';
		const DEFAULT_FORMS_DIR = 'wp-content/themes/the-logical-theme/cf7-forms';
		const TEXT_DOMAIN       = 'cf7-sync';

		public static function init() {
			add_action( 'init', [ __CLASS__, 'load_textdomain' ] );

			if ( defined( 'WP_CLI' ) && WP_CLI ) {
				self::register_cli_command();
			}
		}

		public static function load_textdomain() {
			load_plugin_textdomain(
				self::TEXT_DOMAIN,
				false,
				dirname( plugin_basename( __FILE__ ) ) . '/languages'
			);
		}

		private static function register_cli_command() {
			if ( ! class_exists( 'WP_CLI' ) ) {
				return;
			}

			WP_CLI::add_command( 'cf7 sync', [ __CLASS__, 'handle_sync_command' ] );
		}

		/**
		 * Sync CF7 forms from JSON manifests.
		 *
		 * ## OPTIONS
		 *
		 * [--dir=<path>]
		 * : Directory containing one JSON manifest per form.
		 *
		 * [--slug=<slug>]
		 * : Sync only one form manifest identified by slug.
		 *
		 * [--dry-run]
		 * : Preview create/update/no-change actions without writing to the database.
		 *
		 * ## EXAMPLES
		 *
		 *     wp cf7 sync --dir=wp-content/themes/the-logical-theme/cf7-forms
		 *     wp cf7 sync --slug=contatti --dry-run
		 */
		public static function handle_sync_command( $args, $assoc_args ) {
			unset( $args );

			self::assert_cf7_available();

			$directory = self::resolve_forms_directory( isset( $assoc_args['dir'] ) ? $assoc_args['dir'] : '' );
			$slug      = isset( $assoc_args['slug'] ) ? sanitize_key( (string) $assoc_args['slug'] ) : '';
			$dry_run   = isset( $assoc_args['dry-run'] );

			$manifests = self::load_manifests( $directory, $slug );
			$results   = [];

			foreach ( $manifests as $manifest ) {
				$results[] = self::sync_manifest( $manifest, $dry_run );
			}

			self::render_results( $results, $dry_run );
		}

		private static function assert_cf7_available() {
			if ( ! class_exists( 'WPCF7_ContactForm' ) ) {
				\WP_CLI::error( __( 'Contact Form 7 must be installed and active before using this command.', 'cf7-sync' ) );
			}
		}

		private static function resolve_forms_directory( $directory ) {
			$directory = trim( (string) $directory );

			if ( '' === $directory ) {
				$directory = trailingslashit( ABSPATH ) . self::DEFAULT_FORMS_DIR;
			} elseif ( ! self::is_absolute_path( $directory ) ) {
				$directory = trailingslashit( ABSPATH ) . ltrim( $directory, '/\\' );
			}

			$real_path = realpath( $directory );
			if ( false === $real_path || ! is_dir( $real_path ) ) {
				\WP_CLI::error( sprintf( __( 'Manifest directory not found: %s', 'cf7-sync' ), $directory ) );
			}

			return $real_path;
		}

		private static function is_absolute_path( $path ) {
			return (bool) preg_match( '#^(?:[A-Za-z]:[\\\\/]|/)#', (string) $path );
		}

		private static function load_manifests( $directory, $slug_filter ) {
			$pattern = trailingslashit( $directory ) . '*.json';
			$files   = glob( $pattern );

			if ( false === $files || [] === $files ) {
				\WP_CLI::error( sprintf( __( 'No JSON manifests found in %s', 'cf7-sync' ), $directory ) );
			}

			sort( $files );

			$manifests = [];
			$seen      = [];

			foreach ( $files as $file ) {
				$manifest = self::load_manifest_file( $file );

				if ( $slug_filter && $manifest['slug'] !== $slug_filter ) {
					continue;
				}

				if ( isset( $seen[ $manifest['slug'] ] ) ) {
					\WP_CLI::error(
						sprintf(
							__( 'Duplicate slug "%1$s" found in %2$s and %3$s', 'cf7-sync' ),
							$manifest['slug'],
							$seen[ $manifest['slug'] ],
							$file
						)
					);
				}

				$seen[ $manifest['slug'] ] = $file;
				$manifests[]               = $manifest;
			}

			if ( $slug_filter && [] === $manifests ) {
				\WP_CLI::error( sprintf( __( 'No manifest found for slug "%s"', 'cf7-sync' ), $slug_filter ) );
			}

			return $manifests;
		}

		private static function load_manifest_file( $file ) {
			$raw_json = file_get_contents( $file );
			if ( false === $raw_json ) {
				\WP_CLI::error( sprintf( __( 'Unable to read manifest file: %s', 'cf7-sync' ), $file ) );
			}

			$data = json_decode( $raw_json, true );
			if ( JSON_ERROR_NONE !== json_last_error() ) {
				\WP_CLI::error( sprintf( __( 'Invalid JSON in %1$s: %2$s', 'cf7-sync' ), $file, json_last_error_msg() ) );
			}

			if ( ! is_array( $data ) ) {
				\WP_CLI::error( sprintf( __( 'Manifest must decode to an object: %s', 'cf7-sync' ), $file ) );
			}

			$manifest = [
				'file'                => $file,
				'slug'                => sanitize_key( isset( $data['slug'] ) ? (string) $data['slug'] : '' ),
				'title'               => isset( $data['title'] ) ? sanitize_text_field( (string) $data['title'] ) : '',
				'status'              => isset( $data['status'] ) ? sanitize_key( (string) $data['status'] ) : 'publish',
				'locale'              => isset( $data['locale'] ) ? sanitize_text_field( (string) $data['locale'] ) : determine_locale(),
				'form'                => isset( $data['form'] ) ? (string) $data['form'] : '',
				'mail'                => self::normalize_mail_property( isset( $data['mail'] ) ? $data['mail'] : null, $file, 'mail' ),
				'mail_2'              => self::normalize_mail_property( isset( $data['mail_2'] ) ? $data['mail_2'] : null, $file, 'mail_2' ),
				'messages'            => self::normalize_messages_property( isset( $data['messages'] ) ? $data['messages'] : [] ),
				'additional_settings' => isset( $data['additional_settings'] ) ? (string) $data['additional_settings'] : '',
			];

			if ( '' === $manifest['slug'] ) {
				\WP_CLI::error( sprintf( __( 'Missing or invalid "slug" in %s', 'cf7-sync' ), $file ) );
			}

			if ( '' === $manifest['title'] ) {
				\WP_CLI::error( sprintf( __( 'Missing "title" in %s', 'cf7-sync' ), $file ) );
			}

			if ( '' === trim( $manifest['form'] ) ) {
				\WP_CLI::error( sprintf( __( 'Missing "form" in %s', 'cf7-sync' ), $file ) );
			}

			return $manifest;
		}

		private static function normalize_mail_property( $value, $file, $property_name ) {
			if ( null === $value ) {
				if ( 'mail' === $property_name ) {
					\WP_CLI::error( sprintf( __( 'Missing "%1$s" in %2$s', 'cf7-sync' ), $property_name, $file ) );
				}

				return [];
			}

			if ( ! is_array( $value ) ) {
				\WP_CLI::error( sprintf( __( '"%1$s" must be an object in %2$s', 'cf7-sync' ), $property_name, $file ) );
			}

			$normalized = [
				'active'             => array_key_exists( 'active', $value ) ? (bool) $value['active'] : true,
				'subject'            => isset( $value['subject'] ) ? (string) $value['subject'] : '',
				'sender'             => isset( $value['sender'] ) ? (string) $value['sender'] : '',
				'recipient'          => isset( $value['recipient'] ) ? (string) $value['recipient'] : '',
				'body'               => isset( $value['body'] ) ? (string) $value['body'] : '',
				'additional_headers' => isset( $value['additional_headers'] ) ? (string) $value['additional_headers'] : '',
				'attachments'        => isset( $value['attachments'] ) ? (string) $value['attachments'] : '',
				'use_html'           => array_key_exists( 'use_html', $value ) ? (bool) $value['use_html'] : false,
				'exclude_blank'      => array_key_exists( 'exclude_blank', $value ) ? (bool) $value['exclude_blank'] : false,
			];

			if ( 'mail' === $property_name ) {
				$required_keys = [ 'subject', 'sender', 'recipient', 'body' ];
				foreach ( $required_keys as $required_key ) {
					if ( '' === trim( $normalized[ $required_key ] ) ) {
						\WP_CLI::error( sprintf( __( 'Missing "%1$s.%2$s" in %3$s', 'cf7-sync' ), $property_name, $required_key, $file ) );
					}
				}
			}

			return $normalized;
		}

		private static function normalize_messages_property( $messages ) {
			if ( ! is_array( $messages ) ) {
				return [];
			}

			$normalized = [];

			foreach ( $messages as $key => $value ) {
				$normalized[ (string) $key ] = (string) $value;
			}

			return $normalized;
		}

		private static function sync_manifest( $manifest, $dry_run ) {
			$form = self::get_form_by_slug( $manifest['slug'] );

			if ( ! $form ) {
				return self::create_form( $manifest, $dry_run );
			}

			return self::update_form( $form, $manifest, $dry_run );
		}

		private static function get_form_by_slug( $slug ) {
			$query = new \WP_Query(
				[
					'post_type'              => 'wpcf7_contact_form',
					'post_status'            => 'any',
					'posts_per_page'         => 2,
					'no_found_rows'          => true,
					'ignore_sticky_posts'    => true,
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
					'meta_query'             => [
						[
							'key'   => self::FORM_META_SLUG,
							'value' => $slug,
						],
					],
				]
			);

			$posts = $query->posts;
			if ( count( $posts ) > 1 ) {
				\WP_CLI::error( sprintf( __( 'Multiple CF7 forms found for slug "%s"', 'cf7-sync' ), $slug ) );
			}

			if ( [] === $posts ) {
				return null;
			}

			return \WPCF7_ContactForm::get_instance( $posts[0] );
		}

		private static function create_form( $manifest, $dry_run ) {
			if ( $dry_run ) {
				return [
					'slug'    => $manifest['slug'],
					'post_id' => '-',
					'title'   => $manifest['title'],
					'action'  => 'create',
				];
			}

			$post_id = wp_insert_post(
				[
					'post_type'   => 'wpcf7_contact_form',
					'post_status' => $manifest['status'],
					'post_title'  => $manifest['title'],
				],
				true
			);

			if ( is_wp_error( $post_id ) ) {
				\WP_CLI::error( sprintf( __( 'Unable to create form "%1$s": %2$s', 'cf7-sync' ), $manifest['slug'], $post_id->get_error_message() ) );
			}

			$form = \WPCF7_ContactForm::get_instance( $post_id );
			if ( ! $form ) {
				\WP_CLI::error( sprintf( __( 'Unable to bootstrap CF7 form instance for "%s"', 'cf7-sync' ), $manifest['slug'] ) );
			}

			self::apply_manifest_to_form( $form, $manifest );
			self::save_form( $form, $manifest['slug'] );

			return [
				'slug'    => $manifest['slug'],
				'post_id' => (string) $form->id(),
				'title'   => $manifest['title'],
				'action'  => 'create',
			];
		}

		private static function update_form( $form, $manifest, $dry_run ) {
			$current = self::extract_form_snapshot( $form );
			$target  = self::build_manifest_snapshot( $manifest );

			if ( self::snapshots_equal( $current, $target ) ) {
				return [
					'slug'    => $manifest['slug'],
					'post_id' => (string) $form->id(),
					'title'   => $manifest['title'],
					'action'  => 'no-change',
				];
			}

			if ( ! $dry_run ) {
				self::apply_manifest_to_form( $form, $manifest );
				self::save_form( $form, $manifest['slug'] );
			}

			return [
				'slug'    => $manifest['slug'],
				'post_id' => (string) $form->id(),
				'title'   => $manifest['title'],
				'action'  => 'update',
			];
		}

		private static function apply_manifest_to_form( $form, $manifest ) {
			$form->set_title( $manifest['title'] );
			$form->set_locale( $manifest['locale'] );

			if ( method_exists( $form, 'set_properties' ) ) {
				$form->set_properties(
					[
						'form'                => $manifest['form'],
						'mail'                => $manifest['mail'],
						'mail_2'              => $manifest['mail_2'],
						'messages'            => $manifest['messages'],
						'additional_settings' => $manifest['additional_settings'],
					]
				);
			} else {
				self::update_form_meta_fallback( $form->id(), $manifest );
			}

			if ( method_exists( $form, 'set_status' ) ) {
				$form->set_status( $manifest['status'] );
			} else {
				wp_update_post(
					[
						'ID'          => $form->id(),
						'post_status' => $manifest['status'],
					]
				);
			}
		}

		private static function save_form( $form, $slug ) {
			$result = $form->save();
			if ( false === $result ) {
				\WP_CLI::error( sprintf( __( 'CF7 failed to save form "%s"', 'cf7-sync' ), $slug ) );
			}

			update_post_meta( $form->id(), self::FORM_META_SLUG, $slug );
		}

		private static function update_form_meta_fallback( $post_id, $manifest ) {
			update_post_meta( $post_id, '_form', $manifest['form'] );
			update_post_meta( $post_id, '_mail', $manifest['mail'] );
			update_post_meta( $post_id, '_mail_2', $manifest['mail_2'] );
			update_post_meta( $post_id, '_messages', $manifest['messages'] );
			update_post_meta( $post_id, '_additional_settings', $manifest['additional_settings'] );
			update_post_meta( $post_id, '_locale', $manifest['locale'] );
		}

		private static function extract_form_snapshot( $form ) {
			$properties = method_exists( $form, 'get_properties' ) ? $form->get_properties() : [];

			return [
				'title'               => (string) $form->title(),
				'status'              => get_post_status( $form->id() ) ?: 'publish',
				'locale'              => self::resolve_form_locale( $form, $properties ),
				'form'                => isset( $properties['form'] ) ? (string) $properties['form'] : (string) get_post_meta( $form->id(), '_form', true ),
				'mail'                => self::normalize_existing_mail_property( isset( $properties['mail'] ) ? $properties['mail'] : get_post_meta( $form->id(), '_mail', true ) ),
				'mail_2'              => self::normalize_existing_mail_property( isset( $properties['mail_2'] ) ? $properties['mail_2'] : get_post_meta( $form->id(), '_mail_2', true ) ),
				'messages'            => self::normalize_messages_property( isset( $properties['messages'] ) ? $properties['messages'] : get_post_meta( $form->id(), '_messages', true ) ),
				'additional_settings' => isset( $properties['additional_settings'] ) ? (string) $properties['additional_settings'] : (string) get_post_meta( $form->id(), '_additional_settings', true ),
			];
		}

		private static function resolve_form_locale( $form, $properties ) {
			if ( method_exists( $form, 'locale' ) ) {
				return (string) $form->locale();
			}

			if ( isset( $properties['locale'] ) ) {
				return (string) $properties['locale'];
			}

			return (string) get_post_meta( $form->id(), '_locale', true );
		}

		private static function normalize_existing_mail_property( $mail ) {
			if ( ! is_array( $mail ) ) {
				return [];
			}

			return [
				'active'             => array_key_exists( 'active', $mail ) ? (bool) $mail['active'] : true,
				'subject'            => isset( $mail['subject'] ) ? (string) $mail['subject'] : '',
				'sender'             => isset( $mail['sender'] ) ? (string) $mail['sender'] : '',
				'recipient'          => isset( $mail['recipient'] ) ? (string) $mail['recipient'] : '',
				'body'               => isset( $mail['body'] ) ? (string) $mail['body'] : '',
				'additional_headers' => isset( $mail['additional_headers'] ) ? (string) $mail['additional_headers'] : '',
				'attachments'        => isset( $mail['attachments'] ) ? (string) $mail['attachments'] : '',
				'use_html'           => array_key_exists( 'use_html', $mail ) ? (bool) $mail['use_html'] : false,
				'exclude_blank'      => array_key_exists( 'exclude_blank', $mail ) ? (bool) $mail['exclude_blank'] : false,
			];
		}

		private static function build_manifest_snapshot( $manifest ) {
			return [
				'title'               => $manifest['title'],
				'status'              => $manifest['status'],
				'locale'              => $manifest['locale'],
				'form'                => $manifest['form'],
				'mail'                => $manifest['mail'],
				'mail_2'              => $manifest['mail_2'],
				'messages'            => $manifest['messages'],
				'additional_settings' => $manifest['additional_settings'],
			];
		}

		private static function snapshots_equal( $current, $target ) {
			return wp_json_encode( $current ) === wp_json_encode( $target );
		}

		private static function render_results( $results, $dry_run ) {
			$items = [];

			foreach ( $results as $result ) {
				$items[] = [
					'slug'    => $result['slug'],
					'post_id' => $result['post_id'],
					'action'  => $result['action'],
					'title'   => $result['title'],
				];
			}

			\WP_CLI\Utils\format_items( 'table', $items, [ 'slug', 'post_id', 'action', 'title' ] );

			$counts = [
				'create'    => 0,
				'update'    => 0,
				'no-change' => 0,
			];

			foreach ( $results as $result ) {
				if ( isset( $counts[ $result['action'] ] ) ) {
					$counts[ $result['action'] ]++;
				}
			}

			$message = sprintf(
				__( 'CF7 sync complete%s. Created: %d, Updated: %d, Unchanged: %d', 'cf7-sync' ),
				$dry_run ? ' ' . __( '(dry-run)', 'cf7-sync' ) : '',
				$counts['create'],
				$counts['update'],
				$counts['no-change']
			);

			\WP_CLI::success( $message );
		}
	}

	CF7_Sync_Plugin::init();
}
