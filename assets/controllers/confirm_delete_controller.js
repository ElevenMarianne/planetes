import { Controller } from '@hotwired/stimulus';

/**
 * Interception de soumission de formulaire avec confirmation native.
 * Remplace les onsubmit="return confirm('...')" inline.
 *
 * Usage :
 *   <form method="post" action="..."
 *         data-controller="confirm-delete"
 *         data-confirm-delete-message-value="Supprimer cette planète ?">
 *     <button type="submit">Supprimer</button>
 *   </form>
 */
export default class extends Controller {
    static values = {
        message: { type: String, default: 'Confirmer la suppression ?' },
    };

    connect() {
        this.element.addEventListener('submit', this.handleSubmit.bind(this));
    }

    disconnect() {
        this.element.removeEventListener('submit', this.handleSubmit.bind(this));
    }

    handleSubmit(event) {
        if (!window.confirm(this.messageValue)) {
            event.preventDefault();
            event.stopImmediatePropagation();
        }
    }
}
