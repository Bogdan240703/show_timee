// assets/controllers/band_selector_controller.js
import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["bandDropdownButton", "bandDropdown", "bandList", "selectedBandsText", "searchInput"];
    static values = {
        bandsData: Array,
        selectedBandIds: Array
    };

    connect() {

        this.selectedBands = new Set(this.selectedBandIdsValue.map(id => id.toString()));
        this.originalSelect = this.element.querySelector('[name*="[bands]"]');

        this.renderBands();
        this.updateDisplay();
        document.addEventListener('click', this.documentClick);
    }

    disconnect() {
        document.removeEventListener('click', this.documentClick);
    }

    renderBands() {
        if (!this.bandListTarget) {
            return;
        }
        this.bandListTarget.innerHTML = '';

        this.bandsDataValue.forEach(band => {
            const li = document.createElement('li');
            const isSelected = this.selectedBands.has(band.id.toString());

            li.innerHTML = `
                <div class="flex items-center p-2 rounded hover:bg-base-200">
                    <input
                        id="band-${band.id}"
                        type="checkbox"
                        value="${band.id}"
                        ${isSelected ? 'checked' : ''}
                        class="checkbox checkbox-sm"
                        data-band-id="${band.id}"
                        data-action="change->band-selector#handleCheckboxChange"
                    >
                    <label for="band-${band.id}" class="w-full ml-2 text-sm cursor-pointer">
                        ${band.name}
                    </label>
                </div>
            `;
            this.bandListTarget.appendChild(li);
        });
    }

    toggleDropdown(event) {
        event.preventDefault();
        event.stopPropagation();
        if (this.bandDropdownTarget) {
            this.bandDropdownTarget.classList.toggle('hidden');
        }
    }

    handleCheckboxChange(event) {
        const bandId = event.target.getAttribute('data-band-id');

        if (event.target.checked) {
            this.selectedBands.add(bandId);
        } else {
            this.selectedBands.delete(bandId);
        }

        this.updateDisplay();
        this.updateOriginalSelect();
    }

    documentClick = (e) => {
        if (this.element && !this.element.contains(e.target) && this.hasBandDropdownTarget) {
            this.bandDropdownTarget.classList.add('hidden');
        }
    };

    updateDisplay() {
        const count = this.selectedBands.size;
        if (this.hasSelectedBandsTextTarget) {
            if (count === 0) {
                this.selectedBandsTextTarget.textContent = 'Select Bands';
            } else {
                const selectedBandNames = this.bandsDataValue
                    .filter(band => this.selectedBands.has(band.id.toString()))
                    .map(band => band.name);

                if (count === 1) {
                    this.selectedBandsTextTarget.textContent = selectedBandNames[0];
                } else {
                    this.selectedBandsTextTarget.textContent = `${count} bands selected`;
                }
            }
        }
    }

    filterBands() {
        const searchTerm = this.searchInputTarget.value.toLowerCase();

        const bandItems = this.bandListTarget.querySelectorAll("li");
        bandItems.forEach(item => {
            const label = item.textContent.toLowerCase();
            item.style.display = label.includes(searchTerm) ? "block" : "none";
        });
    }

    updateOriginalSelect() {
        if (this.originalSelect) {
            Array.from(this.originalSelect.options).forEach(option => {
                option.selected = false;
            });

            this.selectedBands.forEach(bandId => {
                const option = this.originalSelect.querySelector(`option[value="${bandId}"]`);
                if (option) {
                    option.selected = true;
                }
            });
        }
    }
}


