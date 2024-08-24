import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["innercontainer", "overlay", "closebutton"]

    async close() {
        this.innercontainerTarget.classList.add("-tw-translate-x-full")
        this.innercontainerTarget.classList.remove("tw-translate-x-0")
        this.overlayTarget.classList.add("tw-opacity-0")
        this.overlayTarget.classList.remove("tw-opacity-100")
        this.closebuttonTarget.classList.add("tw-opacity-0")
        this.closebuttonTarget.classList.remove("tw-opacity-100")
        await this.#waitForAnimation()
        this.element.classList.add('tw-hidden')
    }

    async open() {
        this.element.classList.remove('tw-hidden')
        await new Promise(res => setTimeout(res, 1));
        this.innercontainerTarget.classList.add("tw-translate-x-0")
        this.overlayTarget.classList.add("tw-opacity-100")
        this.innercontainerTarget.classList.remove("-tw-translate-x-full")
        this.overlayTarget.classList.remove("tw-opacity-0")
        await this.#waitForAnimation()
        this.closebuttonTarget.classList.add("tw-opacity-100")
        this.closebuttonTarget.classList.remove("tw-opacity-0")

    }

    #waitForAnimation() {
        return Promise.all(
            this.innercontainerTarget.getAnimations().map(animation => animation.finished),
        )
    }
}
