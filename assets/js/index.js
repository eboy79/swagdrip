import gsap from 'gsap';
import { initializeScripts } from './main.js';
import './page-load-info.js';
import initInfiniteScroll from './infinite-scroll';

document.addEventListener('DOMContentLoaded', function() {
    initializeScripts();
    initInfiniteScroll();
});