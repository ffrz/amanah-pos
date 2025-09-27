/**
 * Composable function to wrap a row click handler, preventing the click
 * event from firing if the user is currently selecting text.
 * * @returns {Object} An object containing the protectClick function.
 */
export function useTableClickProtection() {
    /**
     * Wraps the original row click handler with text selection protection.
     * * @param {Function} originalClickHandler - The function to execute on a legitimate row click.
     * @returns {Function} A new function (row, event) => {...} to be used in @click.
     */
    const protectClick = (originalClickHandler) => (row, event) => {
        // 1. Dapatkan objek seleksi teks saat ini dari DOM
        const selection = window.getSelection();

        // 2. Cek apakah ada rentang teks yang terseleksi (isCollapsed = false)
        if (selection && !selection.isCollapsed) {
            // Teks sedang di-highlight (user bermaksud menyalin).

            // Hentikan event agar tidak memicu aksi lain yang mungkin ada
            event.stopPropagation();
            // console.log("Protection: Click dicegah karena teks sedang diseleksi.");
            return;
        }

        // 3. Jika tidak ada seleksi, jalankan fungsi handler yang asli
        originalClickHandler(row, event);
    };

    return {
        protectClick,
    };
}
