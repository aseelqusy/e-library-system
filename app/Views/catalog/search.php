<?php View::includeLayout('header', ['title' => $title]); ?>
<?php View::includeLayout('navbar'); ?>

<div class="page-background"></div>
<div class="page-wrapper" style="padding-top: var(--navbar-height);">
    <!-- Floating decorations -->
    <div class="floating-decorations" aria-hidden="true"></div>
    <section class="section">
        <div class="container">
            <div class="section-header">
                <h2 class="text-gradient">Search Results</h2>
            </div>

            <form action="<?= url('catalog/search') ?>" method="GET" class="mb-6">
                <div class="search-input-wrap" style="max-width:500px;">
                    <div class="input-group">
                        <span class="input-icon">🔍</span>
                        <input type="text" id="catalog-search" name="q" class="form-control" placeholder="Search books..."
                               value="<?= e($query) ?>" autofocus aria-label="Search books">
                    </div>
                    <div id="catalog-search-suggestions" class="search-suggestions" role="listbox" aria-label="Search suggestions"></div>
                </div>
            </form>

            <?php if ($query): ?>
                <p class="text-muted mb-4"><?= count($books) ?> result(s) for "<strong><?= e($query) ?></strong>"</p>

                <?php if (empty($books)): ?>
                    <div class="text-center" style="padding:60px 0;">
                        <p style="font-size:3rem;margin-bottom:12px;">📭</p>
                        <h3>No books found</h3>
                        <p class="text-muted">Try searching with different keywords.</p>
                    </div>
                <?php else: ?>
                    <div class="book-grid">
                         <?php foreach ($books as $book): ?>
                             <?php
                                 $coverPath = getBookCover($book);
                             ?>
                             <a href="<?= url('books/' . $book['id']) ?>" class="book-card glass-card">
                                 <div class="book-cover">
                                     <?php if ($coverPath): ?>
                                         <img src="<?= e($coverPath) ?>"
                                              alt="<?= e($book['title']) ?> cover"
                                              loading="lazy"
                                              data-book-id="<?= $book['id'] ?>"
                                              data-isbn="<?= e($book['isbn'] ?? '') ?>"
                                              onerror="this.style.display='none'; this.parentElement.innerHTML='📖';">
                                     <?php else: ?>
                                         📖
                                     <?php endif; ?>
                                 </div>
                                <div class="book-info">
                                    <h4 class="book-title"><?= e($book['title']) ?></h4>
                                    <p class="book-author"><?= e($book['author']) ?></p>
                                    <div class="book-meta">
                                        <span class="book-rating">★ <?= $book['rating'] ?></span>
                                    </div>
                                </div>
                                <div class="card-overlay">
                                    <span class="btn btn-primary btn-sm">View Details</span>
                                    <span class="btn btn-outline btn-sm">Buy Now</span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="text-center" style="padding:60px 0;">
                    <p style="font-size:3rem;margin-bottom:12px;">🔍</p>
                    <h3>Search our library</h3>
                    <p class="text-muted">Find books by title, author, or keyword.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php View::includeLayout('footer'); ?>
