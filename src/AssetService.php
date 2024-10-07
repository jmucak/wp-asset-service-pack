<?php

namespace jmucak\wpAssetServicePack;

class AssetService {
	private string $base_url = '';
	private string $base_path = '';

	public function __construct( array $config ) {
		$this->base_path = $config['base_path'];
		$this->base_url  = $config['base_url'];
	}

	private function get_asset_url( string $path ): string {
		return $this->base_url . $path;
	}

	private function get_asset_path( string $path ): string {
		return $this->base_path . $path;
	}

	public function register_site_assets( array $assets ): void {
		add_action( 'wp_enqueue_scripts', function () use ( $assets ) {
			$this->register( $assets );
		} );
	}

	public function register_editor_assets( array $assets ): void {
		add_action( 'enqueue_block_editor_assets', function () use ( $assets ) {
			$this->register( $assets );
		} );
	}

	public function register_admin_assets( array $assets ): void {
		add_action( 'admin_enqueue_scripts', function () use ( $assets ) {
			$this->register( $assets );
		} );
	}

	public function register( array $assets ): void {
		if ( ! empty( $assets['js'] ) ) {
			$this->enqueue_scripts( $assets['js'] );
		}

		if ( ! empty( $assets['css'] ) ) {
			$this->enqueue_styles( $assets['css'] );
		}
	}

	private function enqueue_scripts( array $assets ): void {
		foreach ( $assets as $handle => $data ) {
			if ( empty( $data['path'] ) ) {
				continue;
			}
			if ( ! file_exists( $this->get_asset_path( $data['path'] ) ) ) {
				continue;
			}

			$version        = $data['version'] ?? 1.0;
			$timestamp_bust = ! empty( $data['timestamp_bust'] );
			$in_footer      = $data['in_footer'] ?? true;

			if ( $timestamp_bust ) {
				$version .= sprintf( '.%d', filemtime( $this->get_asset_path( $data['path'] ) ) );
			}

			wp_enqueue_script( $handle, $this->get_asset_url( $data['path'] ), [], $version, $in_footer );

			if ( ! empty( $data['localize'] ) && ! empty( $data['localize']['data'] ) ) {
				wp_localize_script( $handle, $data['localize']['object'], $data['localize']['data'] );
			}
		}
	}

	private function enqueue_styles( array $assets ): void {
		foreach ( $assets as $handle => $data ) {
			if ( empty( $data['path'] ) ) {
				continue;
			}

			wp_enqueue_style( $handle, $this->get_asset_url( $data['path'] ), [], $data['version'] ?? 1.0, $data['in_footer'] ?? true );
		}
	}
}