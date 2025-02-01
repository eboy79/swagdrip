// infinite-scroll.js
export default function initInfiniteScroll() {
    const container = document.getElementById('infinite-scroll-container');
    const loadingSpinner = document.getElementById('loading-spinner');
    
    if (!container || !loadingSpinner) return;

    let page = 2;
    let isLoading = false;
    let hasMore = true;
    let currentRequest = null;
    
    async function loadMorePosts() {
        if (isLoading || !hasMore) return;
        
        isLoading = true;
        loadingSpinner.style.display = 'flex'; // Changed from classList
        
        try {
            // Remove the old request if it exists
            if (currentRequest) {
                currentRequest = null;
            }

            const response = await fetch(
                `${window.mnrInfiniteScroll.restUrl}?page=${page}&per_page=4`,
                {
                    headers: {
                        'X-WP-Nonce': window.mnrInfiniteScroll.nonce
                    }
                }
            );
            
            // Get total pages from WordPress headers
            const totalPages = parseInt(response.headers.get('X-WP-TotalPages'));
            
            // Check if we've reached the end
            if (response.status === 400 || page > totalPages) {
                hasMore = false;
                loadingSpinner.style.display = 'none';
                return;
            }
            
            const posts = await response.json();
            
            if (!posts || posts.length === 0) {
                hasMore = false;
                loadingSpinner.style.display = 'none';
                return;
            }
            
            posts.forEach(post => {
                const date = new Date(post.date);
                const formattedDate = `${date.getFullYear()}.${String(date.getMonth() + 1).padStart(2, '0')}.${String(date.getDate()).padStart(2, '0')}::${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}:${String(date.getSeconds()).padStart(2, '0')}`;
                
                const article = document.createElement('article');
                article.className = 'post';
                article.innerHTML = `
                    <a href="${post.link}" class="post-link no-underline">
                        <h2 class="post-title">${post.title.rendered}</h2>
                        <div class="post-date">
                            <span class="date-value">
                                ${formattedDate}
                            </span>
                        </div>
                        <p class="post-description">${post.excerpt.rendered}</p>
                        <div class="parametric-brick-holdr">
                            <div class="parametric-brick">
                                <div class="parametric-brick__face"></div>
                                <div class="parametric-brick__side parametric-brick__side--left"></div>
                                <div class="parametric-brick__side parametric-brick__side--top"></div>
                                <div class="parametric-brick__content">READ MORE</div>
                                <div class="parametric-brick__corners"></div>
                                <div class="parametric-brick__corners-bottom"></div>
                            </div>
                        </div>
                    </a>
                `;
                
                container.appendChild(article);
            });
            
            page++;
            
        } catch (error) {
            console.warn('Reached end of posts or encountered an error:', error);
            hasMore = false;
        } finally {
            isLoading = false;
            currentRequest = null;
            loadingSpinner.style.display = 'none';
        }
    }

    // Improved scroll handler with better throttling
    let isThrottled = false;
    window.addEventListener('scroll', () => {
        if (!hasMore || isLoading || isThrottled) return;
        
        isThrottled = true;
        
        const scrollPosition = window.innerHeight + window.scrollY;
        const threshold = document.documentElement.scrollHeight - 1000;
        
        if (scrollPosition >= threshold) {
            loadMorePosts();
        }
        
        // Reset throttle after 250ms
        setTimeout(() => {
            isThrottled = false;
        }, 250);
    });

    // Initial load
    loadMorePosts();
}