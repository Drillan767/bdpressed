<script setup lang="ts">
import DescriptionBlock from '@/Components/DescriptionBlock.vue'
import VisitorsLayout from '@/Layouts/VisitorsLayout.vue'
import { useHead } from '@vueuse/head'

interface Props {
    description_url: string
    description_text: string
}

defineOptions({ layout: VisitorsLayout })

defineProps<Props>()

useHead({
    title: 'Accueil',
})
</script>

<template>
    <VContainer>
        <VRow class="mb-8">
            <VCol class="d-flex justify-center">
                <DescriptionBlock
                    :image="description_url"
                    :message="description_text"
                />
            </VCol>
        </VRow>
        <VRow>
            <VCol>
                <VCard class="bede-block">
                    <VCardText class="bede-text">
                        <h1 class="mb-2">
                            <VIcon icon="mdi-grid-large" />
                            Bédés
                        </h1>
                        <p>
                            I'm baby letterpress chia pabst waistcoat single-origin coffee deep v knausgaard. Subway tile microdosing roof party kitsch pitchfork jianbing cliche mlkshk flexitarian air plant man braid iceland before they sold out. Sus palo santo next level, tumeric retro authentic four dollar toast actually prism 3 wolf moon fingerstache vinyl hot chicken. Raw denim banjo mixtape organic, flannel hammock wolf street art subway tile vape. Beard gatekeep artisan retro squid asymmetrical.
                        </p>
                        <p>
                            Selvage put a bird on it man bun ramps portland leggings. Vaporware sustainable hashtag, celiac PBR&B cardigan af normcore pug butcher jean shorts roof party wolf. Williamsburg neutra 90's echo park hexagon iceland gastropub typewriter salvia taxidermy bushwick knausgaard brunch 3 wolf moon. Jean shorts polaroid prism vibecession. Deep v tonx truffaut copper mug cronut readymade poutine keytar. Tbh aesthetic messenger bag, sriracha quinoa sustainable mumblecore paleo fingerstache. Roof party heirloom hexagon gluten-free.
                        </p>
                        <p>
                            Knausgaard chartreuse pug meggings. Schlitz selvage air plant jianbing viral kickstarter actually waistcoat slow-carb try-hard. Hammock mlkshk chartreuse swag literally pinterest typewriter skateboard offal tousled yuccie lyft you probably haven't heard of them JOMO. Cred etsy next level, affogato JOMO poutine gochujang marxism praxis air plant fam four dollar toast austin PBR&B hashtag. YOLO taiyaki drinking vinegar freegan tousled, master cleanse 8-bit health goth tumeric. Coloring book post-ironic raw denim wayfarers fanny pack twee.
                        </p>
                        <p>
                            Raw denim tumblr meditation flannel big mood waistcoat vice wayfarers yr franzen solarpunk unicorn. Messenger bag cronut enamel pin cold-pressed, occupy sartorial food truck tote bag normcore kitsch. Hammock chartreuse flannel, tote bag gochujang kombucha YOLO cred mumblecore seitan copper mug. Sus succulents fit squid 3 wolf moon, tote bag gochujang green juice VHS chartreuse literally tumeric vexillologist XOXO.
                        </p>
                        <p>
                            VHS tattooed yuccie, butcher tbh coloring book narwhal post-ironic hella flannel cliche kombucha iceland tumeric. Chicharrones pinterest roof party 8-bit, butcher XOXO letterpress scenester yr. Aesthetic umami +1 lomo gorpcore DIY tonx street art humblebrag shoreditch forage leggings asymmetrical. Cornhole chillwave lomo bitters selfies marxism hella XOXO cloud bread. Stumptown chartreuse semiotics, grailed umami beard mustache godard tousled yes plz skateboard shoreditch. Crucifix sustainable asymmetrical cardigan jean shorts actually raclette. Jianbing blackbird spyplane offal farm-to-table vexillologist lo-fi chartreuse you probably haven't heard of them cronut.
                        </p>
                        <p>
                            Edison bulb mustache enamel pin same everyday carry fingerstache taxidermy. Hella ramps distillery, gluten-free hoodie XOXO salvia put a bird on it schlitz four loko. Sus beard cliche, man bun yes plz semiotics JOMO godard crucifix. Artisan cronut pug four dollar toast fam beard. La croix art party vegan hell of fit keffiyeh.
                        </p>
                        <p>
                            Occupy bicycle rights actually craft beer lyft. Hexagon mumblecore vibecession cardigan subway tile venmo street art, DSA Brooklyn 90's ugh. Taiyaki cray edison bulb, mumblecore vaporware plaid microdosing. Fingerstache tilde fam cornhole. Wolf hammock echo park bushwick photo booth letterpress flexitarian grailed.
                        </p>
                        <p>
                            Celiac cardigan photo booth williamsburg. Semiotics cloud bread actually same, vegan seitan vexillologist 3 wolf moon. Ethical tote bag tousled tbh neutra vinyl ugh master cleanse williamsburg next level kinfolk single-origin coffee chillwave squid. Fingerstache neutral milk hotel ramps church-key. Organic keffiyeh selfies blog. Activated charcoal twee cold-pressed aesthetic you probably haven't heard of them schlitz 90's hammock butcher. Unicorn selvage gatekeep drinking vinegar polaroid.
                        </p>
                        <p>
                            Gluten-free wolf +1 edison bulb gochujang, single-origin coffee banjo church-key normcore tousled blog butcher echo park. Lyft gochujang man braid hashtag. Actually umami blog shoreditch, mixtape schlitz 90's hoodie street art pour-over williamsburg pickled banh mi. Aesthetic glossier gluten-free gatekeep taxidermy.
                        </p>
                        <p>
                            Gluten-free vape godard, cornhole crucifix tousled shaman tilde lumbersexual fixie jean shorts blue bottle direct trade roof party wolf. Bespoke portland bruh pitchfork green juice meditation adaptogen chartreuse YOLO fanny pack waistcoat succulents small batch. Williamsburg narwhal la croix, health goth PBR&B cloud bread mlkshk listicle vibecession man bun. Freegan gorpcore hexagon fam shoreditch. Sus cronut chartreuse XOXO.
                        </p>
                        <p>
                            Ramps meh kombucha, post-ironic migas shaman kinfolk. Gorpcore mustache JOMO schlitz man braid williamsburg. Gorpcore tattooed flannel man bun pop-up lyft af iPhone chillwave poutine. Butcher shaman 3 wolf moon lumbersexual gluten-free. Biodiesel umami praxis neutral milk hotel pour-over Brooklyn succulents quinoa. Master cleanse copper mug you probably haven't heard of them adaptogen iceland deep v.
                        </p>
                        <p>
                            Tilde live-edge franzen truffaut YOLO. Subway tile aesthetic solarpunk, cray la croix chambray direct trade etsy tofu austin tbh quinoa. Praxis hoodie sustainable, unicorn enamel pin kombucha venmo meh echo park. Leggings photo booth deep v jianbing, kogi paleo mumblecore farm-to-table pabst cupping post-ironic. Bicycle rights DIY echo park williamsburg ramps. Succulents tousled hell of, 90's cray sartorial banjo yr.
                        </p>
                        <p>
                            Locavore godard bespoke, chicharrones pinterest actually meggings cray hammock slow-carb banjo stumptown ramps messenger bag. Put a bird on it disrupt coloring book freegan affogato, kickstarter sus. Skateboard pop-up four dollar toast tumblr humblebrag vaporware blog venmo pork belly aesthetic wayfarers. Cronut listicle hashtag live-edge poke. Gastropub meggings viral, yuccie portland cornhole vinyl wolf readymade mlkshk craft beer mustache tumblr edison bulb cray.
                        </p>
                        <p>
                            Church-key wayfarers fanny pack cred distillery hot chicken lomo vaporware dreamcatcher XOXO williamsburg gluten-free chicharrones. Hammock skateboard occupy ascot tote bag four dollar toast portland street art authentic pok pok aesthetic. Ramps asymmetrical VHS flannel, cray gastropub mumblecore heirloom williamsburg authentic kickstarter praxis. Everyday carry yuccie cliche gastropub jianbing. Coloring book cray snackwave, mixtape shabby chic succulents bruh tacos brunch try-hard flexitarian. Typewriter man bun praxis, asymmetrical biodiesel retro paleo migas twee freegan same unicorn taxidermy banh mi. Helvetica banjo post-ironic 90's sustainable asymmetrical.
                        </p>
                        <p>
                            Whatever celiac flexitarian enamel pin kogi. Shoreditch kitsch salvia roof party vegan iPhone cronut vinyl tacos XOXO sriracha paleo yr. Hammock cred mixtape readymade blue bottle tofu franzen chicharrones forage authentic echo park. Taiyaki poutine hexagon kogi, hammock adaptogen ennui pork belly williamsburg readymade tilde praxis. +1 cred gentrify skateboard tilde selfies selvage keffiyeh plaid narwhal. DIY pop-up squid fixie food truck tilde fashion axe wolf 8-bit sustainable plaid beard chambray.
                        </p>
                    </VCardText>
                </VCard>
            </VCol>
        </VRow>
    </VContainer>
</template>
