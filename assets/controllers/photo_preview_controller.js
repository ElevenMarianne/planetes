import { Controller } from '@hotwired/stimulus';

/**
 * Remplace l'input file natif par un bouton stylé + aperçu de la photo.
 *
 * Usage :
 *   <div data-controller="photo-preview">
 *     <img data-photo-preview-target="preview" src="...">
 *     <button type="button" data-photo-preview-target="trigger" data-action="click->photo-preview#open">
 *       Choisir une photo
 *     </button>
 *     <span data-photo-preview-target="filename"></span>
 *     <input type="file" name="photo" accept="image/*"
 *            data-photo-preview-target="input"
 *            data-action="change->photo-preview#preview">
 *   </div>
 */
export default class extends Controller {
    static targets = ['preview', 'input', 'trigger', 'filename'];

    open() {
        this.inputTarget.click();
    }

    preview(event) {
        const file = event.target.files[0];
        if (!file) return;

        // Afficher le nom du fichier
        if (this.hasFilenameTarget) {
            this.filenameTarget.textContent = file.name;
        }

        // Aperçu image
        const reader = new FileReader();
        reader.onload = (e) => {
            if (this.hasPreviewTarget) {
                this.previewTarget.src = e.target.result;
                this.previewTarget.style.display = 'block';
            }
        };
        reader.readAsDataURL(file);
    }
}
