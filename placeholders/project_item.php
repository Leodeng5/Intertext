<!-- TODO: Color based on role -->
<a href="#" class="row project-item sample">
<div class="row project-details">
    <div>
        <span class="project-link col-10">Project 1 Title</span>
        <span class="project-role col-2 mobile-only">Maintainer</span>
        <span class="project-role col-2 not-mobile">M</span>
    </div>
    <span class="project-owner">by Owner One</span>
</div>
</a>

<!-- TODO: Color based on role -->
<a href="#" class="row project-item sample">
<div class="row project-details">
    <div>
        <span class="project-link col-10">Project 2 Title</span>
        <span class="project-role col-2 mobile-only">Maintainer</span>
        <span class="project-role col-2 not-mobile">M</span>
    </div>
    <span class="project-owner">by Owner One</span>
</div>
</a>

<!-- TODO: Color based on role -->
<a href="#" class="row project-item sample">
<div class="row project-details">
    <div>
        <span class="project-link col-10">Project 3 Title Very Long Title Very Very Very Very Very Very Very Very Long</span>
        <span class="project-role col-2 mobile-only">Contributor</span>
        <span class="project-role col-2 not-mobile">C</span>
    </div>
    <span class="project-owner">by Owner Two</span>
</div>
</a>


<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script>
    $(".sample").on("click", function(e){
        e.preventDefault();
        window.alert("Please login to explore this feature");
    });
</script>