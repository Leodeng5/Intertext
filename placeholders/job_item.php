<!-- Sample Job Items -->
<a href="#" class="row job-item sample">
    <div class="row job-details">
        <div>
            <span class="job-link col-10">Sample Job 1</span>
            <span class="job-status col-2 mobile-only">In Progress</span>
            <span class="job-status col-2 not-mobile">WIP</span>
        </div>
        <span class="job-project">for Project X</span>
    </div>
</a>

<a href="#" class="row job-item sample">
    <div class="row job-details">
        <div>
            <span class="job-link col-10">Sample Job 2</span>
            <span class="job-status col-2 mobile-only">Submitted</span>
            <span class="job-status col-2 not-mobile">SUB</span>
        </div>
        <span class="job-project">for Project X</span>
    </div>
</a>
<a href="#" class="row job-item sample">
    <div class="row job-details">
        <div>
            <span class="job-link col-10">Sample Job 3 Longer Title</span>
            <span class="job-status col-2 mobile-only">Closed</span>
            <span class="job-status col-2 not-mobile">C</span>
        </div>
        <span class="job-project sample">for Project Y</span>
    </div>
</a>

<script>
    $(".sample").on("click", function(e){
        e.preventDefault();
        window.alert("Please login to explore this feature");
    });
</script>