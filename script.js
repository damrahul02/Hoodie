


document.getElementById("ropeColor").addEventListener("change", function () {
    const hoodieImage = document.getElementById("hoodieImage");
    if (this.value === "white") {
        hoodieImage.src = "https://via.placeholder.com/500x500?text=White+Rope";
        hoodieImage.alt = "Hoodie with White Rope";
    } else {
        hoodieImage.src = "./Untitled design.png";
        hoodieImage.alt = "Hoodie with Black Rope";
    }
});