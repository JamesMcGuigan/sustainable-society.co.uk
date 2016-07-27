<?php

@include_once('debug/inc.debug.php');

include_once('inc/class.db.php'); // creates $connection;
include_once('inc/inc.string.php');
require_once('config/html.listing.php');
require_once('config/sql.listing.php');

function pagetitle()
{
 echo "Home";
}

function content()
{
  global $db ;
  $entries = homepage_SQL('*');
  homepageHTML_all($entries);

  // print_old_index_htm_page();
}

include_once('templates/sustainable-society.xml.php');


function print_old_index_htm_page() {
echo <<<ENDOFHTML
<!-- InstanceBeginEditable name="content" -->
      <div style='clear:both;></div>
      <div class="homepage_extra">
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td>

          <table width="100%" border="0" cellspacing="5" cellpadding="0" valign="top" bgcolor="#FFFFFF">
          <td>
              <tr>
                <td>

          <table width="100%" border="0" cellspacing="0" cellpadding="10" valign="top" bgcolor="#FFFFFF">
              <tr>
                            <td width="50%" valign="top">
                              <span class="firebrick">NEF's Local Sustainability Bill<br>
                              </span><br>
                                The Local Sustainability Bill is a piece of legislation aimed at promoting the environmental, economic, political and social future of our communities. It is written for local authorities or Regional Development Agencies and asks them to put sustainability issues at the heart of their planning agenda. By sustainability we mean policies that work towards the long-term well-being of any given area. That means promoting local economic needs &#8211; so money stays in (particularly deprived) areas as well as flows out; that the impact on the environment of any planning or economic plans is central to the decision-making process and that the political and social participation and importance of every member of the community is promoted.<br>
                                <a href="http://www.neweconomics.org/default.asp?strRequest=areasofwork&pageid=162" target="_blank">Read more</a></td>
                <td valign="top">
                  <h4 class="firebrick">
&quot;Creating a Sustainable Society&quot; series of events</h4>
                  "Creating a Sustainable Society" was a series of monthly events that took place between March 2002 and May 2003. The aim was to create a synergy among people and organizations working towards a better society but in different fields. Each event had a different theme, such as "Empires of the 21st Century", "Rebuilding Local Economies - From Theory to Practice" and "Monetary Reform, Economic Justice and Political Democracy" and they where presented by a host of distinguished speakers including, Helena Norbert-Hodge, Teresa Hale, Ann Pettifor, James Robertson and Ian Mason.

A report has been written on each of the events.<br>
<b><a href="eventsSS.htm" target="_blank">Read the Reports</a></b><br>

                  </td>
              </tr>
            </table>
                  </td>
              </tr>
          </table>

                  <table width="100%" border="0" cellspacing="0" cellpadding="10">

        <tr>

                <td valign="top">

                  <table width="100%" border="0"  colspan="2" cellpadding="5">

                          <tr>
                            <td>
<ul>
                                <li>
                                  <p><a href="http://www.indictrans.org" target="_blank">Indictrans
                                    Team</a><br>
                                    A voluntary organization based in Mumbai,
                                    India that support NGOs, government institutions,
                                    educational institutions in use of ICT with
                                    free software including that in local content
                                    and with local language enabled software.
                                    They bring out live CD, (general purpose as
                                    well as customized) called gnubhaaratii with
                                    Hindi, Marathi, Gujarati and English interfaces.
                                    They also maintain the present website (sustainable
                                    society.co.uk directory). They have their
                                    website www.indictrans.org Convenor: Prof.
                                    Jitendra Shah. (jitendras{at}vsnl{dot}com)
                                </li>
                              </ul>

                      </td>



                    </tr>

                  </table>



          </td>

        </tr>

      </table>
     <table width="100%" border="0" cellspacing="5" cellpadding="0" align="center" valign="top" bgcolor="#ff7b00">
          <tr><td>
      <table width="100%" border="0" cellspacing="0" cellpadding="10" align="center" valign="top" bgcolor="#FFFFFF">
          <tr><td><p><b>Article</b> </p>
                              <h4><span class="firebrick"><b>Global Capitalism in Crisis Globalisation and Business for the Common Good: Theology and Economics working together
</b>
                    </span>by Kamran Mofid</h4>
                              <p>"... through the teachings of the neo-liberal ideology, we have created a globalised world in

which we have all been dehumanised and turned into producers and consumers devoid of any true

human values; where the main cultural activities are: shop 'til you drop; obsession with oneself

and with celebrity; watching 24 hour junk television; eating junk food and the promotion of

hopelessness and helplessness in that there is no alternative to the current junk way of life."
<br>

                                "... today, in the new dispensation, [economic life] has been declared a moral-free zone. In

shaking ourselves free from many forms of tyrannies, we have achieved one kind of emancipation,

but in the process we have delivered ourselves into the hands of a philosophy which has

destroyed the basis for any common social purpose by emancipating economic activity from the

realm of moral regulation. In the world today, the main problems are not economic or

technological. What is really wrong with modern society, is the fact that it is morally sick.
<br>
Today, similar to what R. H. Tawney had described as 'acquisitive' societies, the whole

tendency, interest and preoccupation is to promote the acquisition of wealth. Rights are

divorced from the performance of functions and the unrestricted pursuit of economic

self-interest is the ruling ethos. A society of this kind which has taken the moral brakes off,

assures that the individuals see no ends other than their own ends, no law other than their own

law and desires and no limit other than that which they think advisable. Thus, it makes the

individual the centre of his/her own universe, and dissolves moral principles into a choice of

expediencies. "
<br>
                                "If we succeed in aligning the most powerful force in capitalism, namely profit, with social,

moral, ethical and spiritual objectives, by bringing economics and theology together and make

them jointly work for the common good, then, the world will be a much better and safer place and

globalisation will become a force for good. If we interlink theology, economics and business, we

can make these subjects far more effective than if they were continued to be studied, as they

are now, in isolation and separately from each other. Therefore, in this sense, we should not

seek to reject economics, politics, business, profit, trade, etc per. se. We should only seek

the globalisation for the common good, where everybody becomes a stakeholder and where everybody

benefits."
<br>
                                "The universal values inherent in all the great religious systems of the world need to be

clearly articulated in terms of contemporary consciousness and the compulsions of the global

society. For this, it is necessary to highlight the golden thread of mysticism and gnosis that

runs through all the great religions of the world. Whether it is the glowing vision of the great

Upanishadic seers or the Jam Tirthankars, the luminous sayings of the Buddha or the passionate

outpourings of the Muslim Sufis, the noble utterances of the great Rabbis, or of the Sikh Gurus,

the inspired utterances of the Christian saints or the insights of the Chinese sages, these and

other traditions of ecstatic union with the Divine represent an important dimension of religion.

It is, in fact, this spiritual dimension that ultimately links all human beings into one, great

extended family - Vasudaiva Kutumbakam - as the Vedas have it. Fanning the glowing spark of

potential divinity within each person irrespective of race or religion, sex or nationality, into

the blazing fire of spiritual realisation is, indeed, the true role of the great religions of

humankind."

<br>
                    <a href="http://www.transnational.org/forum/meet/2002/Mofid_Capitalism.html" target="_blank">Read the article</a>




      </td>
                          </tr></table>
      </td></tr></table>
     <br>
     <table width="100%" border="0" cellspacing="5" cellpadding="0" align="center" valign="top" bgcolor="#ff7b00">
          <tr><td>
      <table width="100%" border="0" cellspacing="0" cellpadding="10" align="center" valign="top" bgcolor="#FFFFFF">
          <tr><td><p><b>Article</b> </p>
                              <h4><span class="firebrick"><b>WHO IS IN CHARGE? A Tiny, Unelected Group, Supported by Powerful, Unrepresentative Minorities </b>
                    </span>by Edward Said</h4>
                              <p>The Bush administration's relentless unilateral march towards war is profoundly disturbing for many reasons, but so far as American citizens are concerned the whole grotesque show is a tremendous failure in democracy. An immensely wealthy and powerful republic has been hijacked by a small cabal of individuals, all of them unelected and therefore unresponsive to public pressure, and simply turned on its head. It is no exaggeration to say that this war is the most unpopular in modern history.<br>
                    <a href="http://www.commondreams.org/views03/0308-07.htm" target="_blank">Read the article</a>




      </td>
                          </tr></table>
      </td></tr></table>     <br>
     <table width="100%" border="0" cellspacing="5" cellpadding="0" align="center" valign="top" bgcolor="#FFFFFF">
          <tr><td>
      <table width="100%" border="0" cellspacing="0" cellpadding="10" align="center" valign="top" bgcolor="#FFFFFF">
          <tr><td><p><b>A poem</b> <br>
                  </p>
                              <h4 class="firebrick">Why are we here?  [ London Feb 15th 2003 ]
</h4>
                              <p>Because we want world security<br>
                    not war; we anticipate afoot an <br>
                    uprising of the soul of sanity,<br>
                    not a conflictual political protest.<br>
                    <br>
                    We march our global cri de coeur<br>
                    for justice in all structures;<br>
                    a yearning to end poverty,<br>
                    not a provocation to war.<br>
                    <br>
                    We see sanity receding <br>
                    as imbecilic mechanistic logic rules;<br>
                    a cause for people world-wide gathering<br>
                    for peace, the majority now interceding.<br>
                    <br>
                    We want one world secure <br>
                    in honoured rich diversity,<br>
                    sane in its mutual trading,<br>
                    sensible to inclusiveness.<br>
                    <br>
                    We march peaceably<br>
                    unable to sit idly by or <br>
                    rush like lemmings to despair -<br>
                    we ask to be heard response-ably.<br>
                    <br>
                    We echo Seattle, Genoa, Port Alegro <br>
                    and a world-wide crescendo of assemblies, <br>
                    compelled 'by the insistence of spirit'<br>
                    called by 'the authority of nature'.<br>
                    <br>
                    We believe in the genuine <br>
                    power of powerlessness,<br>
                    not the power of exploitation;<br>
                    thus we march in longing.<br>
                    <br>
                    Unarmed we share responsibility <br>
                    for the world entire <br>
                    and in our magnanimity want <br>
                    none excluded from the abundance<br>
                    of this awesome planet earth. <br>
                    <br>
                    In a world in deep disarray,<br>
                    terrorism is despair triumphant,<br>
                    justice the only committed hope...<br>
                    for there is no way to peace<br>
                    peace is the way.<br>
                    <br>
                    Peter Challen.<br>
                    Feb 15th 2003
                              <p>From A SHORT ANTHOLOGY ON ASSEMBLIES FOR PEACE WORLD-WIDE<br>
                    <a href="peacepoems.htm" target="_blank">more poems</a><br>




      </td>
                          </tr></table>
      </td></tr></table>     <br>
     <table width="100%" border="0" cellspacing="5" cellpadding="0" align="center" valign="top" bgcolor="#FFFFFF">
          <tr><td>
      <table width="100%" border="0" cellspacing="0" cellpadding="10" align="center" valign="top" bgcolor="#FFFFFF">
          <tr><td><h4 class="firebrick">The Earth Charter Community Summit</h4>
                              <p>&quot;We are privileged to live at the most existing moment
                    in the whole of human history. For this is the moment when
                    we are being called by the deep forces of creation to awaken
                    to a new consciousness of our new possibilities and to embrace
                    the responsibilities that go with our collective presence
                    of the living jewel of life called Earth. We have the need
                    and the means to create a true Earth community. The choice
                    is ours. The time is now. We're the ones we've been waiting
                    for.&quot;<br>
                    <b>David Korten</b>'s finishing keynote address to the <a href="http://www.pcdf.org/2002/earthcommunity.htm" target="_blank">Earth
                    Charter Community Summit</a>.</p>



      </td>
                          </tr></table>
      </td></tr></table>     <br>
     <table width="100%" border="0" cellspacing="5" cellpadding="0" align="center" valign="top" bgcolor="#FFFFFF">
          <tr><td>
      <table width="100%" border="0" cellspacing="0" cellpadding="10" align="center" valign="top" bgcolor="#FFFFFF">
          <tr><td><h4 class="firebrick">Florence builds a bridge to a brave new social paradise</h4>
                              <p><b>by John Vidal - The Guardian</b><br>
                    In 1425, the powerful wool merchants' guild of Florence commissioned
                    the artist Lorenzo Ghiberti to construct a door for the baptistry
                    of St John in the city. He was to "do whatsoever he desired
                    and designed so that it should be the most perfect and most
                    beautiful imaginable". Ghiberti took 27 years and did not
                    disappoint. His doors were described by Michelangelo as worthy
                    of being called the "gates of paradise".<br>
                    Last week in Florence, a similar kind of open-ended brief,
                    to imagine and construct a European social edifice worthy
                    of being one day called a 21st-century paradise, was entrusted
                    to the institutions, politicians and people of Europe. It
                    came from 40,000 intellectuals, students, ecological and social
                    activists, people representing the poorest and most marginalised,
                    radical economists, concerned individuals, humanitarians,
                    artists,culturalists, churches, scientists and land workers
                    from a bewildering array of non-government groups and grassroots
                    social movements. <br>
                    With the title, Another Europe is Possible, and under the
                    banner of the European Social Forum, the many social movements
                    and groups that have demonstrated in Seattle, Genoa, Prague,
                    London and a dozen other cities over the past three years
                    - against world leaders and organisations such as the International
                    Monetary Fund or the World Trade Organisation - set out to
                    show that they could actually propose change and not simply
                    oppose what is happening around the world.<br>
                    <b><a href="http://www.guardian.co.uk/guardianpolitics/story/0,3605,837564,00.html" target="_blank">Read
                    the full article</a></b></p>



      </td>
                          </tr></table>
      </td></tr></table>     <br>
     <table width="100%" border="0" cellspacing="5" cellpadding="0" align="center" valign="top" bgcolor="#FFFFFF">
          <tr><td>
      <table width="100%" border="0" cellspacing="0" cellpadding="10" align="center" valign="top" bgcolor="#FFFFFF">
          <tr><td><h4 class="firebrick">EARTH EMERGENCY - A CALL TO ACTION</h4>
                              <p>Following the inconclusive outcome of the 2002 World Summit
                    on Sustainable Development in Johannesburg, the failure of
                    governments and global institutions to address social and
                    environmental problems is glaringly obvious. We can no longer
                    afford a global system that aims to represent us only in our
                    narrow capacity as consumers. <br>
                    The <b>Earth Emergency - A Call to Action</b> initiative together
                    with the <b>World Future Council</b> will seek to link up
                    civil society initiatives around the world to promote the
                    creation of an Earth Democracy forum for people around the
                    world.<br>
                    A political and corporate 'bypass' is intended, due to the
                    tendency of self-interested parties to prevent appropriate
                    reforms, which address social and environmental problems,
                    from being implemented.<b> The Earth Emergency</b> link up
                    will enable grassroots initiatives based upon sustainable
                    lifestyles to be directly in touch with an overall global
                    council, the<b> World Future Council</b>, whose role will
                    be to provide moral guidance at the highest level. This will
                    ensure both a bottom up and top down approach to reforms so
                    that our political leaders are pressurised to take appropriate
                    action by both the people and a moral leadership. More at<a href="http://www.earthemergency.org" target="_blank">
                    Earth Emergency - A Call to Action</a></p>



      </td>
                          </tr></table>
      </td></tr></table>     <br>
     <table width="100%" border="0" cellspacing="5" cellpadding="0" align="center" valign="top" bgcolor="#ff7b00">
          <tr><td>
      <table width="100%" border="0" cellspacing="0" cellpadding="10" align="center" valign="top" bgcolor="#FFFFFF">
          <tr><td><p><b>Books</b> </p>
                              <h4><span class="firebrick"><b>'Global Forces. A guide for enlightened
                    leaders - what companies and individuals can do'</b>
                    </span>by Bruce Nixon</h4>
                              <p>Everyone has heard of globalisation, but what does it really mean? How does it actually affect businesses and their prospects for future development, and how should leaders be adapting to take advantage of the new opportunities, while contributing to a fairer and more sustainable world?<br>
                    <a href
="books/forces.htm" target="_blank">more</a><b><br>
                    </b><a href="books.htm">Books</a> </p>
                              <h4><span class="firebrick">'eGaia, Growing a peaceful, sustainable
                    Earth through communications' </span>by
                    Gary Alexander</h4>
                              <p>Gary Alexander has written a challenging new book setting
                    out a Utopian yet practical agenda for change that harnesses
                    the exciting potential of electronic communication. It offers
                    a path to a future based upon principles of collaboration
                    and sustainability using information systems to link communities
                    and co-operatives, a future with a co-operative free-market
                    economy driven directly by the health of the environment and
                    the well being of all of humanity rather than money flows.<br>
                    <a href
="books/egaia.htm" target="_blank">more</a><b><br>
                    </b><a href="books.htm">Books</a> </p>



      </td>
                          </tr></table>
      </td></tr></table>
                  <br>
     <table width="100%" border="0" cellspacing="5" cellpadding="0" align="center" valign="top" bgcolor="#FFFFFF">
          <tr><td>
      <table width="100%" border="0" cellspacing="0" cellpadding="10" align="center" valign="top" bgcolor="#FFFFFF">
          <tr><td>
                              <h4 class="firebrick">The Corporate Responsability Coalition</h4>
                              <p>Amnesty International (UK), CAFOD, Friends of the Earth,
                    New Economics Foundation and Traidcraft have come together
                    to form the CORE Coalition. The coalition&#8217;s aim is to
                    develop popular consensus around the themes of corporate accountability
                    and transparency, and to have legislation passed to ensure
                    corporate accountability and performance.<br>
                                 Visit the
                    <a href="http://www.corporate-responsibility.org/" target="_blank">
                    <b>CORE Coalition</b> website</a></p>
                              </td><td><h4 class="firebrick">Agriculture and Globalisation</h4>
                              <p>Throughout the world, agriculture is in crisis. Farmers are
                    going bankrupt in record numbers, and the rural communities
                    of which they are an integral part are being drained of life.
                    Meanwhile, international trade in food is booming. Every year,
                    the distance between producers and consumers rises, to the
                    point where the average American meal has now travelled more
                    than 1,500 miles before it arrives on the dinner table. These
                    two trends are directly linked. The globalisation of the food
                    economy, while enriching a small number of giant 'agribusinesses',
                    is undermining the welfare of everyone else. What's more,
                    it is a major contributor to increasing CO2 emissions, and
                    therefore to climate change.<b> </b><br>
                                Read
                    about <a href="http://www.isec.org.uk/localfood.html" target="_blank">ISEC Local Food Programme</a>.</p>
                          </tr></table>
      </td></tr></table>     <br>
     <table width="100%" border="0" cellspacing="5" cellpadding="0" align="center" valign="top" bgcolor="#FFFFFF">
          <tr><td>
      <table width="100%" border="0" cellspacing="0" cellpadding="10" align="center" valign="top" bgcolor="#FFFFFF">
          <tr><td><h4 class="firebrick">Restructuring of global politics and economics</h4>
                              <p>The International Forum on Globalization (IFG) is an alliance
                    of sixty leading activists, scholars, economists, researchers
                    and writers formed to stimulate new thinking, joint activity,
                    and public education in response to economic globalization.
                    Representing over 60 organizations in 25 countries, IFG associates
                    come together out of a shared concern that the world's corporate
                    and political leadership is undertaking a restructuring of
                    global politics and economics that may prove as historically
                    significant as any event since the Industrial Revolution.
                    This restructuring is happening at tremendous speed, with
                    little public disclosure of the profound consequences affecting
                    democracy, human welfare, local economies, and the natural
                    world. <br>
                                Read more about <a href="http://www.ifg.org" target="_blank">
                    The International Forum on Globalization. </a></p>



      </td>
                          </tr></table>
      </td></tr></table>     <br>
     <table width="100%" border="0" cellspacing="5" cellpadding="0" align="center" valign="top" bgcolor="#FFFFFF">
          <tr><td>
      <table width="100%" border="0" cellspacing="0" cellpadding="10" align="center" valign="top" bgcolor="#FFFFFF">
          <tr><td valign="top"><h4 class="firebrick">Air pollution and traffic</h4>
                              <p>One solution is to switch from traditional cars to alternative
                    fuel cars such as Hypercars (fuel-cell powered vehicles). <br>
                                Find out more about <a href="http://www.rmi.org/sitepages/pid386.php" target="_blank">
                    Hypercars. </a></p>

      </td><td valign="top"><h4 class="firebrick">GM Technology</h4>
                              <p>Consumers and environmentalists across Europe have taken up
                    the campaign against GMOs. This has received much attention
                    in the British media and we are winning change. Yet these
                    changes will not solve the problem for some of the world's
                    most poor and vulnerable. The World Development Movement (WDM)
                    is concerned that GM crops are being pushed on developing
                    countries, threatening the lives of some of the world's poorest
                    farmers. <br>
                                Find out more from the <a href="http://www.wdm.org.uk" target="_blank">
                    World Development Movement (WDM) </a></p>


      </td>
                          </tr></table>
      </td></tr></table>


          <!-- table base -->


          </td>
        </tr>
      </table>
      </div>
      <!-- InstanceEndEditable -->
ENDOFHTML;
}

?>
