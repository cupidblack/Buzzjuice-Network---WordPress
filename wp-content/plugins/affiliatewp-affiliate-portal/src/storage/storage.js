
const storage = {};

/**
 * Storage instance.
 *
 * Provides access to the storage.
 *
 * @type {{set(*, *): *, get(*, *=): boolean|*, remove(*=): boolean}}
 */
const store = {

	/**
	 * Set.
	 *
	 * Sets a value to storage.
	 *
	 * @param key The key to set
	 * @param value The value to set
	 * @returns {*} The value
	 */
	set( key, value ) {
		storage[key] = value;

		return storage[key];
	},

	/**
	 * Get.
	 *
	 * Retrieves an item from storage, if possible.
	 *
	 * @param key
	 * @param fallback
	 * @returns {boolean|*}
	 */
	get( key, fallback = false ) {
		return undefined === storage[key] ? fallback : storage[key];
	},

	/**
	 * Remove.
	 *
	 * Removes an item from storage, if it exists.
	 *
	 * @param key
	 * @returns {*|boolean}
	 */
	remove( key ) {
		const value = this.get( key );

		if ( undefined !== value ) {
			delete storage[key];
		}

		return value;
	}
}

export default store;