/**
 * @license Copyright (c) 2003-2019, CKSource - Frederico Knabben. All rights reserved.
 * This file is licensed under the terms of the MIT License (see LICENSE.md).
 */

class CustomTrackChangesAdapter {
	constructor( editor ) {
		this.editor = editor;
	}

	init() {
		const trackChangesPlugin = this.editor.plugins.get( 'TrackChanges' );

		// Set the adapter to the `TrackChanges#adapter` property.
		trackChangesPlugin.adapter = {
			/**
			 * Called each time the suggestion data is needed.
			 *
			 * The method should return a promise that resolves with the suggestion data object.
			 *
			 * @param {String} id The ID of a suggestion to get.
			 * @returns {Promise}
			 */
			getSuggestion: id => {
				console.log( 'Getting suggestion', id );

				return fetch( '/suggestions/' + id )
					.then( response => response.json() )
					.then( data => {
						data.createdAt = new Date( data.created_at * 1000 );
						data.authorId = data.user_id;

						return data;
					} );
			},

			/**
			 * Called each time a new suggestion is created.
			 *
			 * The method should save the suggestion data in the database
			 * and return a promise that will be resolved when the save is
			 * completed. If the promise resolves with a suggestion data object,
			 * the suggestion in the editor will be updated using the data from the server.
			 *
			 * The `data` object does not expect the `authorId` property.
			 * For security reasons, the author of the suggestions should be set
			 * on the server side.
			 *
			 * The `data` object does not expect the `createdAt` property either.
			 * You should use the server-side time generator to ensure that all users
			 * see the same date.
			 *
			 * @param {Object} data
			 * @param {String} data.id The suggestion ID.
			 * @param {String} data.type The suggestion type.
			 * @returns {Promise}
			 */
			addSuggestion: data => {
				console.log( 'Suggestion added', data );

				const formData = new FormData();
				formData.append( 'id', data.id );
				formData.append( 'type', data.type );
				formData.append( 'article_id', EXAMPLE.ARTICLE_ID );
				formData.append( 'csrf_token', EXAMPLE.CSRF_TOKEN );

				return fetch( '/suggestions', {
						method: "POST",
						body: formData
					} )
					.then( response => response.json() )
					.then( responseData => {
						return {
							createdAt: new Date( responseData.created_at * 1000 )
						};
					} );
			}
		}
	}
}
