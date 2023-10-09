/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// Any CSS you import will output into a single css file (app.css in this case).
import './styles/app.scss';

import {
    initTE,
    Alert,
    Carousel,
    Collapse,
    Dropdown,
    Ripple
} from "tw-elements";
initTE({ Alert, Carousel, Collapse, Dropdown, Ripple });