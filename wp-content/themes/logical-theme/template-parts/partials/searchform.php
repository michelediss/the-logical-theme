<form role="search" method="get" class="search-form m-0" action="<?php echo esc_url(ltc_home_url('/')); ?>">
    <div class="input-group overflow-hidden">
        <input id="input-search" type="search" class="input-search bg-transparent text-sm form-control py-1 px-0 border-top-0 border-start-0 border-end-0 rounded-0 border-primary text-primary paragraph text-sm"
            placeholder="<?php echo esc_attr__('Cerca...', 'textdomain'); ?>" value="<?php echo get_search_query(); ?>"
            name="s" />
        <button id="button-search" class="button-search bg-transparent border-0 text-primary medium text-primary paragraph text-sm me-2" 
            aria-label="<?php esc_attr_e('Cerca', 'textdomain'); ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 40 40" aria-hidden="true" focusable="false">
                <path fill="var(--primary)" d="M40,36.2l-8.2-8.2c2.2-3,3.4-6.6,3.4-10.4s-1.8-9.1-5.2-12.5C23.2-1.7,12-1.7,5.2,5.2c-6.9,6.9-6.9,18,0,24.9,3.4,3.4,7.9,5.2,12.5,5.2s7.3-1.2,10.4-3.4l8.2,8.2,3.8-3.8ZM8.9,26.3c-4.8-4.8-4.8-12.6,0-17.4,2.4-2.4,5.5-3.6,8.7-3.6s6.3,1.2,8.7,3.6,3.6,5.4,3.6,8.7-1.3,6.4-3.6,8.7c-4.8,4.8-12.6,4.8-17.4,0Z"/>
            </svg>
        </button>
        <div class="overlay-search position-absolute w-100 h-100 top-0 left-0 z-2 bg-transparent"></div>
    </div>
</form>
