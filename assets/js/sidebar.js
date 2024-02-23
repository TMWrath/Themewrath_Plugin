document.addEventListener('DOMContentLoaded', function() {
    var menuBar = document.getElementById('menubar');
    var isMouseOverMenuBar = false;

    document.addEventListener('mousemove', function(e) {
        if (e.clientX < 150 && !isMouseOverMenuBar) { 
            
            menuBar.style.left = '0px';
        } else if (!isMouseOverMenuBar) {
            
            menuBar.style.left = '-250px';
        }
    });

    menuBar.addEventListener('mouseenter', function() {
        isMouseOverMenuBar = true;
        menuBar.style.left = '0px';
    });

    menuBar.addEventListener('mouseleave', function() {
        isMouseOverMenuBar = false;
        menuBar.style.left = '-250px';
    });
});