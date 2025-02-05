<!-- Sidebar -->
<div id="sidebar">
    <button id="closeSidebar" class="sidebar-close">&times;</button>
    <nav>
        <ul>
            <?php
            // Générer dynamiquement la liste des catégories
            $categories = get_categories();
            foreach ($categories as $category) {
                echo '<li><a href="' . get_category_link($category->term_id) . '">' . $category->name . '</a></li>';
            }
            ?>
        </ul>
    </nav>
</div>

<!-- Overlay pour le mobile -->
<div id="sidebarOverlay"></div>

<!-- Bouton pour ouvrir la sidebar -->
<button id="openSidebar" class="sidebar-toggle">&#9776;</button>

<!-- Style (CSS inline ou lien externe) -->
<style>
    body.sidebar-open #sidebar {
        transform: translateX(0);
    }
    body.sidebar-open #sidebarOverlay {
        display: block;
    }
    #sidebar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100%;
        width: 250px;
        background: #111;
        color: #fff;
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }
    #sidebar nav ul {
        list-style: none;
        padding: 0;
    }
    #sidebar nav ul li {
        margin: 1rem 0;
        text-align: center;
    }
    #sidebar nav ul li a {
        color: #fff;
        text-decoration: none;
    }
    #sidebarOverlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 100;
    }
    .sidebar-toggle {
        position: fixed;
        top: 10px;
        left: 10px;
        z-index: 101;
        background: #111;
        color: #fff;
        border: none;
        padding: 10px 15px;
        cursor: pointer;
    }
    .sidebar-close {
        position: absolute;
        top: 10px;
        right: 10px;
        background: none;
        border: none;
        color: #fff;
        font-size: 1.5rem;
        cursor: pointer;
    }
</style>

<!-- Script -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    var openBtn = document.getElementById("openSidebar");
    var closeBtn = document.getElementById("closeSidebar");
    var sidebar = document.getElementById("sidebar");
    var overlay = document.getElementById("sidebarOverlay");
    var body = document.body;

    function toggleSidebar() {
        body.classList.toggle("sidebar-open");
    }

    openBtn.addEventListener("click", toggleSidebar);
    closeBtn.addEventListener("click", toggleSidebar);
    overlay.addEventListener("click", toggleSidebar);
});
</script>
