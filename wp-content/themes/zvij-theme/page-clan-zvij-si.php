<?php
/**
 * Membership explainer; signup stays in the shared footer.
 */
if (! defined('ABSPATH')) {
    exit;
}

$images = get_template_directory_uri() . '/assets/images/';
$rate = defined('ZVIJ_KRISTALI_PER_EUR') ? (int) ZVIJ_KRISTALI_PER_EUR : 100;
$expiry = function_exists('zvij_kristali_expiry_months') ? zvij_kristali_expiry_months() : 12;
get_header();
?>
<article class="zv-members">
  <header class="zv-members__intro">
    <p class="eyebrow">Član Zvij.si</p>
    <h1>Zvijače za zvijače.</h1>
    <p>Robo bi naročil tako ali tako. Zakaj ne bi ob tem nabral še nekaj za naslednjič?</p>
    <p>Kot se na dnu grinderja počasi nabira tisto fino, se ti pri nas z nakupi nabirajo <strong>kristali</strong>. Le da ti naš kupček zniža naslednji račun.</p>
    <a class="button button--lime" href="#postani-clan">Postani član</a>
  </header>

  <section class="zv-members__how" aria-labelledby="kristali-title">
    <h2 id="kristali-title">Malo po malo. Potem pa prav pride.</h2>
    <ol class="zv-members__steps">
      <li>
        <figure class="zv-members__visual">
          <img src="<?php echo esc_url($images . 'kits/black-kit-flatlay.png'); ?>" alt="Izdelki Zvij.si, pripravljeni za tvoj naslednji nakup" width="800" height="800">
        </figure>
        <p class="zv-members__number">01 / Tvoj nakup</p>
        <h3>Izbereš, kar ti manjka.</h3>
        <p>Filtri, papir, grinder … ti veš, kaj rabiš. Pri izdelku vidiš, koliko kristalov dobiš kot član. Naroči z istim e-poštnim naslovom, s katerim si se včlanil.</p>
      </li>
      <li>
        <figure class="zv-members__visual">
          <img src="<?php echo esc_url($images . 'members/grinder-open.webp'); ?>" alt="Razstavljen grinder Zvij.si s sitom in spodnjim predalom za zbiranje" width="800" height="800">
        </figure>
        <p class="zv-members__number">02 / Nekaj se nabere</p>
        <h3>Tvoj mali kupček raste.</h3>
        <p>Ko je naročilo plačano, se kristali pripišejo k tvojemu članstvu. En nakup, pa drugi … kot spodnji predal grinderja. Ni ga treba vsakič izprazniti.</p>
      </li>
      <li>
        <figure class="zv-members__visual zv-members__receipt">
          <div class="zv-members__bill">
            <span class="zv-members__bill-label">Tvoj naslednji nakup</span>
            <div><span>Izdelki</span><span>20,00 €</span></div>
            <div class="zv-members__saving"><span><?php echo esc_html((string) ($rate * 3)); ?> kristalov</span><span>−3,00 €</span></div>
            <div class="zv-members__total"><span>Za izdelke plačaš</span><strong>17,00 €</strong></div>
            <small>Primer porabe · dostava posebej</small>
          </div>
          <figcaption>Kristali v prihranek. Tako preprosto.</figcaption>
        </figure>
        <p class="zv-members__number">03 / Manj na računu</p>
        <h3>Naslednjič plačaš manj.</h3>
        <p>Na blagajni izbereš »Uporabi kristale« in določiš, koliko jih želiš porabiti. Prihranek se takoj odšteje od cene izdelkov. Ostali kristali počakajo na naslednjič.</p>
      </li>
    </ol>
  </section>

  <section class="zv-members__exchange" aria-label="Vrednost kristalov">
    <p><strong><?php echo esc_html((string) $rate); ?> kristalov = 1 €</strong><span>Ni treba računati na prste.</span></p>
    <p><?php echo esc_html((string) ($rate * 3)); ?> kristalov? <b>3 € manj.</b><br><?php echo esc_html((string) ($rate * 5)); ?> kristalov? <b>5 € manj.</b></p>
  </section>

  <section class="zv-members__extras" aria-labelledby="clan-plus">
    <?php if (function_exists('zvij_invitation_url')) : ?>
    <h2>Povabi prijatelja. Naj se nabira obema.</h2>
    <p>V svojem računu najdeš osebno povezavo in QR-kodo, ki ju lahko deliš s prijatelji. Ko si prijatelj prek povabila ustvari račun, se povežeta. Ob njegovih plačanih nakupih ti dodamo še <strong>10 % kristalov, ki jih dobi on</strong>.</p>
    <p>On nabere 100 kristalov. Ti dobiš dodatnih 10. Njemu ostanejo vsi — tvoj kupček pa tudi malo zraste.</p>
    <a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>">Povabila najdeš v svojem računu →</a>
    <p><small>Bonus šteje za neposredno povabljene prijatelje z novim računom in za kristale iz njihovih nakupov. Zaokrožimo ga navzdol na cel kristal, ob stornu nakupa pa ga odštejemo.</small></p>
    <?php endif; ?>
    <h2 id="clan-plus">Pa še kakšna zvijača v nabiralniku.</h2>
    <p>Kdaj ti pošljemo člansko ponudbo, kaj novega ali idejo za naslednji nakup. Če ti ne sede več, se odjaviš z enim klikom.</p>
  </section>

  <section class="zv-members__questions" aria-label="Dobro je vedeti">
    <details>
      <summary>Moram kupiti grinder, da zbiram kristale?</summary>
      <p>Ne. Grinder je samo naša prispodoba za zbiranje. Kristali so tvoje člansko dobroimetje, ki se nabira z nakupi. Fizičnega grinderja ne potrebuješ.</p>
    </details>
    <details>
      <summary>Koliko kristalov dobim za nakup?</summary>
      <p>Odvisno od izdelkov in količine. Število je prikazano pri izdelku, skupni znesek pa v košarici. Kristali se pripišejo, ko je naročilo plačano.</p>
    </details>
    <details>
      <summary>Je pri kristalih kakšen drobni tisk?</summary>
      <p>Z njimi znižaš ceno izdelkov, dostava pa se plača posebej. V gotovino jih ne menjamo. Če <?php echo esc_html((string) $expiry); ?> mesecev ni aktivnosti, kristali potečejo.</p>
    </details>
  </section>
</article>
<?php get_footer(); ?>
