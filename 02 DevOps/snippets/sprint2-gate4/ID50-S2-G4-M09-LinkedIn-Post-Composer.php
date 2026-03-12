<?php
// Snippet ID: 50
// Name: S2-G4-M09 LinkedIn Post Composer
// Scope: global
// Active: True
// Modified: 2026-03-09 22:42:06
// Lines: 160

/**
 * S2-G4-M09 — LinkedIn Post Composer
 * Sprint 2, Gate 4 | GitLab Issue #31
 *
 * Create text + image posts on LinkedIn personal profile.
 *
 * Deploy via: Code Snippets plugin
 * Snippet Title: "S2-G4-M09 LinkedIn Post Composer"
 * Scope: global
 * Priority: 10
 * Depends on: S2-G4-M08 LinkedIn Token Manager
 *
 * @package NonSequitur
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get the authenticated LinkedIn member URN.
 *
 * @return string|WP_Error Person URN (e.g. "urn:li:person:abc123") or error.
 */
function ns_linkedin_get_person_urn() {
    $cached = get_transient( 'ns_linkedin_person_urn' );
    if ( $cached ) return $cached;

    $token = function_exists( 'ns_linkedin_get_token' ) ? ns_linkedin_get_token() : '';
    if ( is_wp_error( $token ) ) return $token;

    $resp = wp_remote_get( 'https://api.linkedin.com/v2/userinfo', array(
        'headers' => array( 'Authorization' => 'Bearer ' . $token ),
        'timeout' => 15,
    ) );

    if ( is_wp_error( $resp ) ) return $resp;

    $body = json_decode( wp_remote_retrieve_body( $resp ), true );
    if ( empty( $body['sub'] ) ) {
        return new WP_Error( 'ns_linkedin_no_sub', 'Could not resolve LinkedIn user.' );
    }

    $urn = 'urn:li:person:' . $body['sub'];
    set_transient( 'ns_linkedin_person_urn', $urn, DAY_IN_SECONDS );
    return $urn;
}

/**
 * Create a post on the authenticated user's LinkedIn profile.
 *
 * @param string $text      Post text.
 * @param string $image_url Optional image URL to include.
 * @return array|WP_Error Post data or error.
 */
function ns_linkedin_create_post( $text, $image_url = '' ) {
    $token = ns_linkedin_get_token();
    if ( is_wp_error( $token ) ) return $token;

    $urn = ns_linkedin_get_person_urn();
    if ( is_wp_error( $urn ) ) return $urn;

    $payload = array(
        'author'         => $urn,
        'lifecycleState' => 'PUBLISHED',
        'visibility'     => array( 'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC' ),
        'specificContent' => array(
            'com.linkedin.ugc.ShareContent' => array(
                'shareCommentary'   => array( 'text' => $text ),
                'shareMediaCategory' => empty( $image_url ) ? 'NONE' : 'IMAGE',
            ),
        ),
    );

    // If image URL provided, register upload and attach
    if ( ! empty( $image_url ) ) {
        $media = ns_linkedin_upload_image( $token, $urn, $image_url );
        if ( ! is_wp_error( $media ) ) {
            $payload['specificContent']['com.linkedin.ugc.ShareContent']['media'] = array( $media );
        }
    }

    $resp = wp_remote_post( 'https://api.linkedin.com/v2/ugcPosts', array(
        'headers' => array(
            'Authorization'             => 'Bearer ' . $token,
            'Content-Type'              => 'application/json',
            'X-Restli-Protocol-Version' => '2.0.0',
        ),
        'body'    => wp_json_encode( $payload ),
        'timeout' => 30,
    ) );

    if ( is_wp_error( $resp ) ) {
        if ( function_exists( 'ns_diag_write' ) ) {
            ns_diag_write( 'error', 'linkedin', 'Post failed', array( 'error' => $resp->get_error_message() ) );
        }
        return $resp;
    }

    $code = wp_remote_retrieve_response_code( $resp );
    if ( $code !== 201 ) {
        if ( function_exists( 'ns_diag_write' ) ) {
            ns_diag_write( 'error', 'linkedin', 'Post rejected', array( 'http_code' => $code ) );
        }
        return new WP_Error( 'ns_linkedin_post_fail', 'LinkedIn post failed (HTTP ' . $code . ').' );
    }

    $post_id = wp_remote_retrieve_header( $resp, 'x-restli-id' );
    if ( function_exists( 'ns_audit_log' ) ) {
        ns_audit_log( 'linkedin_post_created', 'post', $post_id ?: 'unknown', substr( $text, 0, 50 ), array() );
    }

    return array( 'id' => $post_id, 'status' => 'published' );
}

/**
 * Upload image to LinkedIn via registerUpload flow.
 */
function ns_linkedin_upload_image( $token, $urn, $image_url ) {
    // Step 1: Register upload
    $reg_resp = wp_remote_post( 'https://api.linkedin.com/v2/assets?action=registerUpload', array(
        'headers' => array( 'Authorization' => 'Bearer ' . $token, 'Content-Type' => 'application/json' ),
        'body'    => wp_json_encode( array( 'registerUploadRequest' => array(
            'owner'   => $urn,
            'recipes' => array( 'urn:li:digitalmediaRecipe:feedshare-image' ),
            'serviceRelationships' => array( array(
                'identifier'       => 'urn:li:userGeneratedContent',
                'relationshipType' => 'OWNER',
            ) ),
        ) ) ),
        'timeout' => 15,
    ) );

    if ( is_wp_error( $reg_resp ) ) return $reg_resp;
    $reg_body = json_decode( wp_remote_retrieve_body( $reg_resp ), true );
    $upload_url = $reg_body['value']['uploadMechanism']['com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest']['uploadUrl'] ?? '';
    $asset      = $reg_body['value']['asset'] ?? '';

    if ( empty( $upload_url ) || empty( $asset ) ) {
        return new WP_Error( 'ns_linkedin_upload_reg_fail', 'Upload registration failed.' );
    }

    // Step 2: Download + upload image binary
    $img = wp_remote_get( $image_url, array( 'timeout' => 30 ) );
    if ( is_wp_error( $img ) ) return $img;

    wp_remote_request( $upload_url, array(
        'method'  => 'PUT',
        'headers' => array( 'Authorization' => 'Bearer ' . $token ),
        'body'    => wp_remote_retrieve_body( $img ),
        'timeout' => 60,
    ) );

    return array(
        'status'      => 'READY',
        'media'       => $asset,
        'title'       => array( 'text' => 'Image' ),
        'description' => array( 'text' => '' ),
    );
}
