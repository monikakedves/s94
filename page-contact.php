<?php get_header(); ?>

<div class="studio94-contact-layout">
    <div class="studio94-contact-main">
        <h2>Get in touch</h2>
        <p>Got a question about an order, a limited drop, or just want to say hi? Drop us a line using the form. We usually reply within 24 hours (unless we're covered in ink).</p>
        <p>Before you reach out, you might find a quick answer on our <a href="/faq" style="color:var(--brand-pink); font-weight: 500;">FAQ page</a>. Interested in stocking our products or collaborating on a custom project? Select the relevant subject, and let's make something rad together.</p>
        
        <div class="contact-methods">
            <div class="contact-method">
                <strong>Email</strong>
                <a href="mailto:hi@studio94.uk" class="contact-email-link">hi@studio94.uk</a>
            </div>
            <div class="contact-method">
                <strong>Studio Hours</strong>
                <span style="display: block;">Mon - Thu: 10am - 4pm</span>
                <span style="display: block;">Fri - Sun: Closed</span>
            </div>
        </div>
        <div class="contact-actions" style="margin-top: 2.5rem; display: flex; gap: 1rem; flex-wrap: wrap;">
            <a href="/faq" class="btn-faq">Frequently Asked Questions</a>
            <a href="/the-studio" class="btn-studio">About Us</a>
        </div>
    </div>

    <div class="studio94-contact-sidebar">
        <?php 
        while ( have_posts() ) : the_post();
            the_content();
        endwhile; 
        ?>
    </div>
</div>

<div class="page-body-container" style="display: none;">
    <div class="page-content">
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectEls = document.querySelectorAll('.wpforms-form select');
    
    selectEls.forEach(selectEl => {
        const customDropdown = document.createElement('div');
        customDropdown.className = 'custom-wpforms-dropdown custom-dropdown-wrap';

        const selectedDisplay = document.createElement('div');
        selectedDisplay.className = 'custom-dropdown-selected';
        const activeOption = selectEl.options[selectEl.selectedIndex] || selectEl.options[0];
        selectedDisplay.innerHTML = `<span>${activeOption.innerHTML}</span><span class="chevron"></span>`;

        const optionsList = document.createElement('ul');
        optionsList.className = 'custom-dropdown-list';

        Array.from(selectEl.options).forEach(option => {
            if (option.disabled || option.value === '') return;
            
            const li = document.createElement('li');
            li.innerHTML = option.innerHTML; 
            li.dataset.value = option.value;
            
            if (option.selected) {
                li.classList.add('active');
            }

            li.addEventListener('click', function(e) {
                e.stopPropagation();
                
                selectedDisplay.querySelector('span').innerHTML = this.innerHTML;
                
                selectEl.value = this.dataset.value;
                selectEl.dispatchEvent(new Event('change', { bubbles: true }));

                optionsList.querySelectorAll('li').forEach(el => el.classList.remove('active'));
                this.classList.add('active');
                
                customDropdown.classList.remove('open');
            });
            
            optionsList.appendChild(li);
        });

        customDropdown.appendChild(selectedDisplay);
        customDropdown.appendChild(optionsList);
        
        selectEl.parentNode.appendChild(customDropdown);
        selectEl.style.display = 'none';

        selectedDisplay.addEventListener('click', function(e) {
            e.stopPropagation();
            document.querySelectorAll('.custom-dropdown-wrap').forEach(d => {
                if (d !== customDropdown) d.classList.remove('open');
            });
            customDropdown.classList.toggle('open');
        });
    });

    document.addEventListener('click', function() {
        document.querySelectorAll('.custom-dropdown-wrap').forEach(d => d.classList.remove('open'));
    });
});
</script>

<?php get_footer(); ?>