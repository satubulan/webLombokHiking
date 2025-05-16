<section class="hero" style="background-image: url('lombok_hiking_foto/background.jpg'); background-size: cover; background-position: center;">
    <div class="hero-overlay">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">Jelajahi Keindahan Gunung di Lombok</h1>
                <p class="hero-subtitle">Platform open trip pendakian gunung terbaik di Lombok</p>
                <div class="hero-buttons">
                    <a href="trips.php" class="btn btn-primary btn-lg">Lihat Open Trip</a>
                    <a href="#featured" class="btn btn-secondary btn-lg">Destinasi Populer</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="featured" class="featured-trips">
    <div class="container">
        <h2 class="section-title">Trip Mendatang</h2>
        <div class="trip-cards">
            <div class="card trip-card">
                <div class="card-image">
                    <img src="lombok_hiking_foto/rinjani.jpg" alt="Pendakian Gunung Rinjani">
                    <span class="price-tag">Rp 2.500.000</span>
                    <span class="difficulty-badge hard">Hard</span>
                </div>
                <div class="card-content">
                    <h3 class="card-title">Summit Rinjani via Sembalun</h3>
                    <p class="location"><i class="fas fa-map-marker-alt"></i> Gunung Rinjani</p>
                    <p class="date"><i class="far fa-calendar-alt"></i> 10 Jun 2025</p>
                    <p class="duration"><i class="far fa-clock"></i> 3 hari</p>
                    <div class="card-footer">
                        <span class="slots"><i class="fas fa-users"></i> 8/15 slot</span>
                        <a href="trip-detail.php?id=t1" class="btn btn-primary">Detail</a>
                    </div>
                </div>
            </div>
            
            <div class="card trip-card">
                <div class="card-image">
                    <img src="lombok_hiking_foto/pergasingan.jpg" alt="Bukit Pergasingan">
                    <span class="price-tag">Rp 850.000</span>
                    <span class="difficulty-badge easy">Easy</span>
                </div>
                <div class="card-content">
                    <h3 class="card-title">Sunrise Pergasingan Weekend Trip</h3>
                    <p class="location"><i class="fas fa-map-marker-alt"></i> Bukit Pergasingan</p>
                    <p class="date"><i class="far fa-calendar-alt"></i> 15 May 2025</p>
                    <p class="duration"><i class="far fa-clock"></i> 1 hari</p>
                    <div class="card-footer">
                        <span class="slots"><i class="fas fa-users"></i> 12/20 slot</span>
                        <a href="trip-detail.php?id=t2" class="btn btn-primary">Detail</a>
                    </div>
                </div>
            </div>
            
            <div class="card trip-card">
                <div class="card-image">
                    <img src="lombok_hiking_foto/anakdara.jpg" alt="Gunung Anak Dara">
                    <span class="price-tag">Rp 650.000</span>
                    <span class="difficulty-badge moderate">Moderate</span>
                </div>
                <div class="card-content">
                    <h3 class="card-title">Anak Dara One Day Trip</h3>
                    <p class="location"><i class="fas fa-map-marker-alt"></i> Gunung Anak Dara</p>
                    <p class="date"><i class="far fa-calendar-alt"></i> 5 Jun 2025</p>
                    <p class="duration"><i class="far fa-clock"></i> 1 hari</p>
                    <div class="card-footer">
                        <span class="slots"><i class="fas fa-users"></i> 5/12 slot</span>
                        <a href="trip-detail.php?id=t3" class="btn btn-primary">Detail</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="mountain-categories">
    <div class="container">
        <h2 class="section-title">Kategori Gunung</h2>
        <div class="category-cards">
            <div class="category-card" onclick="location.href='categories.php?category=High Peak'">
                <div class="category-icon"><i class="fas fa-mountain"></i></div>
                <h3>Puncak Tinggi</h3>
                <p>Gunung dengan ketinggian di atas 2.000 mdpl</p>
            </div>
            <div class="category-card" onclick="location.href='categories.php?category=Savanna'">
                <div class="category-icon"><i class="fas fa-tree"></i></div>
                <h3>Padang Savana</h3>
                <p>Gunung dengan pemandangan savana yang indah</p>
            </div>
            <div class="category-card" onclick="location.href='categories.php?category=Beginner Friendly'">
                <div class="category-icon"><i class="fas fa-hiking"></i></div>
                <h3>Cocok untuk Pemula</h3>
                <p>Gunung dengan jalur pendakian yang mudah</p>
            </div>
            <div class="category-card" onclick="location.href='categories.php?category=Waterfall'">
                <div class="category-icon"><i class="fas fa-water"></i></div>
                <h3>Air Terjun</h3>
                <p>Gunung dengan air terjun yang memukau</p>
            </div>
        </div>
    </div>
</section>

<section class="top-mountains">
    <div class="container">
        <h2 class="section-title">Gunung Populer di Lombok</h2>
        <div class="mountain-cards">
            <div class="card mountain-card">
                <div class="card-image">
                    <img src="lombok_hiking_foto/rinjani.jpg" alt="Gunung Rinjani">
                    <span class="height-badge">3726m</span>
                    <span class="difficulty-badge hard">Hard</span>
                </div>
                <div class="card-content">
                    <h3 class="card-title">Gunung Rinjani</h3>
                    <p class="location"><i class="fas fa-map-marker-alt"></i> Lombok Timur, Nusa Tenggara Barat</p>
                    <p class="description">Gunung Rinjani adalah gunung tertinggi kedua di Indonesia dengan keindahan danau Segara Anak di kalderanya...</p>
                    <div class="card-footer">
                        <div class="categories">
                            <span class="category-tag">High Peak</span>
                            <span class="category-tag">Lake</span>
                            <span class="category-tag">Volcanic</span>
                        </div>
                        <a href="mountain-detail.php?id=m1" class="btn btn-primary">Detail</a>
                    </div>
                </div>
            </div>
            
            <div class="card mountain-card">
                <div class="card-image">
                    <img src="lombok_hiking_foto/pergasingan.jpg" alt="Bukit Pergasingan">
                    <span class="height-badge">1700m</span>
                    <span class="difficulty-badge easy">Easy</span>
                </div>
                <div class="card-content">
                    <h3 class="card-title">Bukit Pergasingan</h3>
                    <p class="location"><i class="fas fa-map-marker-alt"></i> Sembalun, Lombok Timur</p>
                    <p class="description">Bukit Pergasingan menawarkan pemandangan padang savana yang luas dengan latar belakang Gunung Rinjani...</p>
                    <div class="card-footer">
                        <div class="categories">
                            <span class="category-tag">Beginner Friendly</span>
                            <span class="category-tag">Savanna</span>
                        </div>
                        <a href="mountain-detail.php?id=m2" class="btn btn-primary">Detail</a>
                    </div>
                </div>
            </div>
            
            <div class="card mountain-card">
                <div class="card-image">
                    <img src="lombok_hiking_foto/anakdara.jpg" alt="Gunung Anak Dara">
                    <span class="height-badge">2100m</span>
                    <span class="difficulty-badge moderate">Moderate</span>
                </div>
                <div class="card-content">
                    <h3 class="card-title">Gunung Anak Dara</h3>
                    <p class="location"><i class="fas fa-map-marker-alt"></i> Sembalun, Lombok Timur</p>
                    <p class="description">Gunung Anak Dara menawarkan pemandangan padang rumput yang luas dan pemandangan Gunung Rinjani yang memukau...</p>
                    <div class="card-footer">
                        <div class="categories">
                            <span class="category-tag">Savanna</span>
                            <span class="category-tag">Beginner Friendly</span>
                        </div>
                        <a href="mountain-detail.php?id=m3" class="btn btn-primary">Detail</a>
                    </div>
                </div>
            </div>
            
            <div class="card mountain-card">
                <div class="card-image">
                    <img src="lombok_hiking_foto/bukitselong.jpg" alt="Bukit Selong">
                    <span class="height-badge">600m</span>
                    <span class="difficulty-badge easy">Easy</span>
                </div>
                <div class="card-content">
                    <h3 class="card-title">Bukit Selong</h3>
                    <p class="location"><i class="fas fa-map-marker-alt"></i> Sembalun, Lombok Timur</p>
                    <p class="description">Bukit Selong menawarkan panorama persawahan berbentuk terasering yang memukau...</p>
                    <div class="card-footer">
                        <div class="categories">
                            <span class="category-tag">Beginner Friendly</span>
                            <span class="category-tag">Savanna</span>
                        </div>
                        <a href="mountain-detail.php?id=m4" class="btn btn-primary">Detail</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="featured-guides">
    <div class="container">
        <h2 class="section-title">Guide Berpengalaman</h2>
        <div class="guide-cards">
            <div class="card guide-card">
                <div class="guide-image">
                    <img src="assets/images/guides/guide1.jpg" alt="Ahmad Rinjani">
                </div>
                <div class="guide-content">
                    <h3 class="guide-name">Ahmad Rinjani</h3>
                    <div class="guide-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="guide-experience"><i class="fas fa-award"></i> 10 tahun pengalaman</p>
                    <div class="guide-specialization">
                        <span class="spec-tag">High Peak</span>
                        <span class="spec-tag">Volcanic</span>
                    </div>
                    <a href="guide-detail.php?id=g1" class="btn btn-secondary">Profil Lengkap</a>
                </div>
            </div>
            
            <div class="card guide-card">
                <div class="guide-image">
                    <img src="assets/images/guides/guide2.jpg" alt="Sarah Lombok">
                </div>
                <div class="guide-content">
                    <h3 class="guide-name">Sarah Lombok</h3>
                    <div class="guide-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="far fa-star"></i>
                    </div>
                    <p class="guide-experience"><i class="fas fa-award"></i> 7 tahun pengalaman</p>
                    <div class="guide-specialization">
                        <span class="spec-tag">Beginner Friendly</span>
                        <span class="spec-tag">Savanna</span>
                    </div>
                    <a href="guide-detail.php?id=g2" class="btn btn-secondary">Profil Lengkap</a>
                </div>
            </div>
            
            <div class="card guide-card">
                <div class="guide-image">
                    <img src="assets/images/guides/guide3.jpg" alt="Budi Sembalun">
                </div>
                <div class="guide-content">
                    <h3 class="guide-name">Budi Sembalun</h3>
                    <div class="guide-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="guide-experience"><i class="fas fa-award"></i> 15 tahun pengalaman</p>
                    <div class="guide-specialization">
                        <span class="spec-tag">High Peak</span>
                        <span class="spec-tag">Lake</span>
                        <span class="spec-tag">Volcanic</span>
                    </div>
                    <a href="guide-detail.php?id=g3" class="btn btn-secondary">Profil Lengkap</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="testimonials">
    <div class="container">
        <h2 class="section-title">Testimonial</h2>
        <div class="testimonial-slider">
            <div class="testimonial-slides">
                <div class="testimonial-slide active">
                    <div class="testimonial-content">
                        <div class="quote-icon"><i class="fas fa-quote-left"></i></div>
                        <p>Pengalaman terbaik sepanjang hidup saya! Pemandangannya luar biasa, guide sangat profesional, dan semua perjalanan diatur dengan baik.</p>
                        <div class="testimonial-author">
                            <img src="assets/images/testimonials/user1.jpg" alt="Budi Santoso">
                            <div class="author-info">
                                <h4>Budi Santoso</h4>
                                <p>Pendaki Gunung Rinjani</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="testimonial-slide">
                    <div class="testimonial-content">
                        <div class="quote-icon"><i class="fas fa-quote-left"></i></div>
                        <p>Website yang sangat membantu untuk menemukan open trip dan guide terpercaya. Booking mudah dan cepat!</p>
                        <div class="testimonial-author">
                            <img src="assets/images/testimonials/user2.jpg" alt="Siti Nurhaliza">
                            <div class="author-info">
                                <h4>Siti Nurhaliza</h4>
                                <p>Pendaki Bukit Pergasingan</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="testimonial-slide">
                    <div class="testimonial-content">
                        <div class="quote-icon"><i class="fas fa-quote-left"></i></div>
                        <p>Saya awalnya ragu untuk mendaki, tapi berkat guide profesional dari LombokHiking, perjalanan jadi menyenangkan dan aman.</p>
                        <div class="testimonial-author">
                            <img src="assets/images/testimonials/user3.jpg" alt="Rudi Hartono">
                            <div class="author-info">
                                <h4>Rudi Hartono</h4>
                                <p>Pendaki Anak Dara</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="testimonial-navigation">
                <button class="prev-testimonial"><i class="fas fa-chevron-left"></i></button>
                <div class="testimonial-dots">
                    <span class="dot active" data-slide="0"></span>
                    <span class="dot" data-slide="1"></span>
                    <span class="dot" data-slide="2"></span>
                </div>
                <button class="next-testimonial"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
    </div>
</section>

<section class="cta-section">
    <div class="container">
        <div class="cta-content">
            <h2>Siap Untuk Petualangan Berikutnya?</h2>
            <p>Bergabunglah dengan open trip pendakian kami dan nikmati keindahan alam Lombok</p>
            <a href="trips.php" class="btn btn-primary btn-lg">Lihat Jadwal Trip</a>
        </div>
    </div>
</section>