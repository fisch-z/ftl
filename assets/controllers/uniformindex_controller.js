import {Controller} from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ["textinput", "listitem", "toggleShowAllButton", "toggleShowAllButtonInner", "noresults", "itemscount"]

    showAll = false

    connect() {
        this.filter()
    }

    toggleShowAll() {
        this.showAll = !this.showAll
        if (this.showAll) {
            this.toggleShowAllButtonTarget.classList.add("tw-bg-indigo-600")
            this.toggleShowAllButtonTarget.classList.remove("tw-bg-gray-200")
            this.toggleShowAllButtonInnerTarget.classList.add("tw-translate-x-5")
            this.toggleShowAllButtonInnerTarget.classList.remove("tw-translate-x-0")
        } else {
            this.toggleShowAllButtonTarget.classList.add("tw-bg-gray-200")
            this.toggleShowAllButtonTarget.classList.remove("tw-bg-indigo-600")
            this.toggleShowAllButtonInnerTarget.classList.add("tw-translate-x-0")
            this.toggleShowAllButtonInnerTarget.classList.remove("tw-translate-x-5")
        }
        this.filter()
    }

    filter() {
        let lowerCaseFilterTerm = this.textinputTarget.value.toLowerCase()

        let visibleCount = 0
        this.listitemTargets.forEach((el, i) => {
            let show = true
            if (show && !this.showAll && ["updated", "not-tracked"].includes(el.getAttribute("data-filter-status"))) {
                show = false
            }
            if (show && !el.getAttribute("data-filter-key").includes(lowerCaseFilterTerm)) {
                show = false
            }
            if (show) {
                visibleCount++;
                el.classList.remove("tw-hidden")
            } else {
                el.classList.add("tw-hidden")
            }
        })
        if (visibleCount > 0) {
            this.noresultsTarget.classList.add("tw-hidden")
        } else {
            this.noresultsTarget.classList.remove("tw-hidden")
        }
        this.itemscountTarget.textContent = visibleCount;
    }
}
