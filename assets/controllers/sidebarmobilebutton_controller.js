import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
    }

    click() {
        this.dispatch("click")
    }
}
