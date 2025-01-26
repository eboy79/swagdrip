document.addEventListener('DOMContentLoaded', () => {
    const svgColor = "#fff";
    const radius = 150; // Adjust the radius as needed
    const textContent = "ERIC CLAY MINER";

    function generateCircleText(radius) {
        const svgNS = "http://www.w3.org/2000/svg";
        const svg = document.createElementNS(svgNS, "svg");
        svg.setAttribute("viewBox", `-200 -200 400 400`);
        svg.setAttribute("xmlns", svgNS);
        svg.setAttribute("class", "circle-text");
        svg.setAttribute("height", "100%");
        svg.setAttribute("width", "100%");

        const defs = document.createElementNS(svgNS, "defs");

        const path = document.createElementNS(svgNS, "path");
        path.setAttribute("id", "circlePath");
        path.setAttribute("d", `M 0, ${-radius} A ${radius},${radius} 0 1,1 0,${radius} A ${radius},${radius} 0 1,1 0,${-radius}`);
        defs.appendChild(path);

        const text = document.createElementNS(svgNS, "text");
        text.setAttribute("fill", svgColor);
        text.setAttribute("font-size", "24"); // Adjusted font size
        text.setAttribute("letter-spacing", "0.1em"); // Add some letter spacing if needed

        const textPath = document.createElementNS(svgNS, "textPath");
        textPath.setAttributeNS("http://www.w3.org/1999/xlink", "xlink:href", "#circlePath");
        textPath.setAttribute("startOffset", "50%"); // Adjust starting position as needed
        textPath.setAttribute("text-anchor", "middle");
        textPath.setAttribute("method", "stretch"); // Ensures text stretches along the path
        textPath.textContent = textContent;

        text.appendChild(textPath);
        svg.appendChild(defs);
        svg.appendChild(text);

        return svg;
    }

    function displayCircleText(radius) {
        const container = document.getElementById("wide-container");
        if (container) {
            container.innerHTML = "";
            const svgElement = generateCircleText(radius);
            container.appendChild(svgElement);
        } else {
            console.error("Container with id 'wide-container' not found.");
        }
    }

    displayCircleText(radius);
});
