<?php
/**
 * Seed bodies for the News & Events posts.
 *
 * Article copy and images carried over verbatim from dynamiqes.com (the old site is
 * the SEO benchmark: headings and copy are preserved, only the styling changes).
 * Images live in assets/news/<slug>/ ; %ASSETS% resolves to DQ_URI . '/assets' at
 * seed time so post_content holds ordinary absolute URLs.
 *
 * @package dynamiqes
 */

/** Article body HTML for a seeded news slug, or '' when there is none. */
function dq_default_news_body( $slug ) {
	static $bodies = null;
	if ( null === $bodies ) {
		$bodies = array(
		/* DynamIQ Hosts Exclusive Event at SAP Philippines to Empower SMBs with SAP Business One Solutions — October 28, 2025 (live: /news-event/dynamiq-empowers-smbs-sap-business-one-event-2025/) */
		'dynamiq-hosts-exclusive-event-at-sap-philippines' => <<<'HTML'
<p>DynamIQ Enterprise Solution Inc. hosted an exclusive event at SAP Philippines Inc. last October 10, 2025. The event focused on empowering small and medium-sized businesses (SMBs) through SAP Business One solutions.</p>
<p><img src="%ASSETS%/news/dynamiq-hosts-exclusive-event-at-sap-philippines/slide1.jpg" alt="DynamIQ's event on SAP B1 solutions" /></p>
<p>The event brought together business leaders and decision-makers to explore how SAP Business One can streamline operations, boost efficiency, and drive sustainable growth.</p>
<p>A key highlight of the event was the launch of two new SAP Business One add-ons:</p>
<ul>
<li>LMS, designed to help organizations ensure employees meet standards while tracking learning outcomes across departments.</li>
<li>IQ Ai, a next-generation innovation that empowers smarter and faster decision-making by reshaping how businesses operate.</li>
</ul>
<p><img src="%ASSETS%/news/dynamiq-hosts-exclusive-event-at-sap-philippines/slide23.jpg" alt="Attendees of DynamIQ's Build to Grow " /></p>
<p>The gathering was attended by professionals from various industries, including business owners, IT leaders, accounting executives, and operations managers. The session opened with a welcome message from Mr. Alfie B. Amontos, setting the tone for an insightful and forward-thinking discussion on the future of intelligent business solutions.</p>
HTML,
		/* Forest Foundation Philippines — March 28, 2025 (live: /news-event/forest-foundation-philippines-partnership/) */
		'forest-foundation-philippines' => <<<'HTML'
<h2>Let’s Grow a Greener Future Together!</h2>
<p>Forests are the lungs of our planet, and every tree we plant helps protect biodiversity, combat climate change, and provide cleaner air and water.</p>
<p>We recently contributed to reforestation efforts in the Philippines, a step toward a more sustainable world. But there’s still so much more to do!</p>
<p><img src="%ASSETS%/news/forest-foundation-philippines/viber-image-2025-01-23-16-25-05-611.jpg" alt="" /></p>
<h2>Why Plant Trees?</h2>
<ul>
<li>Trees absorb carbon dioxide and produce oxygen.</li>
<li>They provide habitats for countless species.</li>
<li>Forests prevent soil erosion and reduce the risk of flooding.</li>
</ul>
<p><img src="%ASSETS%/news/forest-foundation-philippines/viber-image-2025-01-23-16-25-06-715.jpg" alt="" /></p>
<h2>How You Can Help:</h2>
<ol>
<li> Plant trees in your community or backyard.</li>
<li> Support reforestation programs and organizations.</li>
<li> Reduce paper waste and recycle.</li>
<li> Spread the word about forest conservation.</li>
</ol>
<p><img src="%ASSETS%/news/forest-foundation-philippines/img-1746.jpg" alt="" /></p>
<p>Let’s protect our forests and leave a legacy of hope for future generations.</p>
<p>Thank you, Forest Foundation Philippines, for partnering with Dynamiq to protect, restore, and conserve our Philippine forests.</p>
        </div>
    </div>
HTML,
		/* DynamIQ Enterprise Solution Inc. Brings Hope and Happiness to National Children’s Hospital — February 12, 2025 (live: /news-event/dynamiq-enterprise-solution-inc-brings-hope-and-happiness-to-national-childrens-hospital/) */
		'brings-hope-and-happiness-to-national-childrens-hospital' => <<<'HTML'
<p>For the second consecutive year, DynamIQ Enterprise Solution Inc. has extended its commitment to community service by visiting the National Children’s Hospital. More than just a corporate social responsibility initiative, this visit was a heartfelt mission to uplift the spirits of young patients, offer prayers for their healing, and express deep gratitude to the medical professionals who dedicate their lives to care and service.</p>
<p><img src="%ASSETS%/news/brings-hope-and-happiness-to-national-childrens-hospital/img-0552-1.jpg" alt="" /></p>
<p>Upon arrival, the DynamIQ team was warmly welcomed by hospital representatives, who shared insights into the daily struggles and triumphs of both the children and healthcare staff. These conversations shed light on the resilience of young patients battling various illnesses and the unwavering dedication of doctors, nurses, and caregivers who work tirelessly to provide them with the best possible care.</p>
<p>Determined to bring hope and comfort, the DynamIQ team personally distributed carefully curated care packages filled with toys, books, and essential supplies. More than the material gifts, the visit was an opportunity for genuine connection—reading stories, playing games, and sharing moments of encouragement.</p>
<p><img src="%ASSETS%/news/brings-hope-and-happiness-to-national-childrens-hospital/img-0595-1.jpg" alt="" /></p>
<p>A particularly moving part of the visit was when the DynamIQ team gathered to pray for the children, their families, and the hospital staff. With hands joined and hearts united in faith, they lifted prayers for healing, strength, and hope, trusting in God’s grace to bring comfort and miracles to those in need. The atmosphere was filled with warmth and peace as the team reminded the children and staff that they are not alone in their journey.</p>
<p>The medical staff, often working under immense pressure, also received words of encouragement and appreciation from DynamIQ, recognizing their dedication and sacrifices. Their work extends beyond medical treatment; they offer comfort, reassurance, and hope to families during their most challenging times.</p>
<p><img src="%ASSETS%/news/brings-hope-and-happiness-to-national-childrens-hospital/img-0525-1.jpg" alt="" /></p>
<p>As DynamIQ Enterprise Solution Inc. continues to grow, so does its commitment to making a lasting impact on communities in need. The company firmly believes that success is measured not just by achievements in business but by the positive difference made in the lives of others. This visit is just one of many ways DynamIQ seeks to spread kindness, inspire faith, and uphold its mission of serving beyond the corporate world.</p>
<p>To God always be the Glory!</p>
        </div>
    </div>
HTML,
		/* Channel Partner Event 2024 – Synergy: One Partnership, Countless Possibilities — July 3, 2024 (live: /news-event/channel-partner-event-2024-synergy-one-partnership-countless-possibilities/) */
		'channel-partner-event-2024-synergy' => <<<'HTML'
<p>On February 22, 2024, SAP Business One enthusiasts gathered at the Buttery &amp; Co. in Quezon City for the much-anticipated Channel Partner Event 2024, titled &#8220;Synergy: One Partnership, Countless Possibilities.&#8221; The event was a resounding success, bringing together industry leaders, experts, and partners to explore the future of SAP Business One and the dynamic opportunities presented by the DynamIQ partnership program.</p>
<p><img src="%ASSETS%/news/channel-partner-event-2024-synergy/synergy-2024-3.jpg" alt="" /></p>
<p>The event kicked off with an insightful presentation on the future road map of SAP Business One. Attendees were introduced to upcoming features and innovations that promise to revolutionize business operations. The session provided a comprehensive overview of strategic updates, highlighting how businesses can leverage these advancements to stay competitive and drive growth.<br />
One of the event&#8217;s key segments was the introduction of new product add-ons for SAP Business One. These add-ons are designed to enhance the system&#8217;s functionality, offering tailored solutions to optimize various business processes. Participants were given a firsthand look at these tools, with demonstrations showcasing their potential to improve efficiency and deliver superior value.</p>
<p>The final session focused on the DynamIQ Partnership Program, outlining its numerous benefits. The program offers a range of opportunities for business development, access to exclusive resources, and a collaborative environment for growth. Attendees learned how partnering with DynamIQ can open new avenues for innovation and expansion.</p>
<p><img src="%ASSETS%/news/channel-partner-event-2024-synergy/synergy-2024-56.jpg" alt="" /></p>
<p>The Channel Partner Event 2024 provided a platform for meaningful dialogue and knowledge sharing among SAP Business One users and partners. Participants left with a deeper understanding of the software&#8217;s future direction, the latest add-ons, and the advantages of the DynamIQ partnership program. The event underscored the importance of collaboration and continuous innovation in maintaining a competitive edge in today&#8217;s fast-paced business environment.</p>
<p>The Channel Partner Event 2024 &#8211; Synergy: One Partnership, Countless Possibilities, held at Buttery &amp; Co., was a testament to the power of collaboration and innovation. It provided a glimpse into the future of SAP Business One and demonstrated the immense potential of strategic partnerships.</p>
<p>As we look forward to future events, this gathering has set a high benchmark, promising continued growth and success for all participants.<br />
For more information on upcoming events and to stay updated with the latest in SAP Business One, visit our website <a href="https://dynamiqes.com" target="_blank" rel="noopener">https://dynamiqes.com</a>/</p>
        </div>
    </div>
HTML,
		);
	}
	if ( empty( $bodies[ $slug ] ) ) {
		return '';
	}
	return str_replace( '%ASSETS%', DQ_URI . '/assets', $bodies[ $slug ] );
}

/** True when a seeded post still carries the title-only placeholder body. */
function dq_news_body_is_placeholder( $post ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return false;
	}
	// decode entities and unify apostrophes: the placeholder was written with esc_html(),
	// so "Children's" is stored as "Children&#039;s" and would never match the title as-is
	$norm = function ( $t ) { return trim( str_replace( array( "\u{2019}", "\u{2018}" ), "'", html_entity_decode( wp_strip_all_tags( $t ), ENT_QUOTES, 'UTF-8' ) ) ); };
	$text = $norm( $post->post_content );
	return '' === $text || $text === $norm( $post->post_title );
}
