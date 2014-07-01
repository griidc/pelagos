package edu.tamucc.hri.griidc.xml;

import java.io.File;
import java.math.BigInteger;
import java.util.List;

import javax.xml.bind.JAXBContext;
import javax.xml.bind.JAXBException;
import javax.xml.bind.Unmarshaller;

import edu.tamucc.hri.griidc.pubs.Publication;
import edu.tamucc.hri.griidc.pubs.jaxb.Genre;
import edu.tamucc.hri.griidc.pubs.jaxb.Identifier;
import edu.tamucc.hri.griidc.pubs.jaxb.Mods;
import edu.tamucc.hri.griidc.pubs.jaxb.Name;
import edu.tamucc.hri.griidc.pubs.jaxb.NamePart;
import edu.tamucc.hri.griidc.pubs.jaxb.Records;
import edu.tamucc.hri.griidc.pubs.jaxb.SearchRetrieveResponse;
import edu.tamucc.hri.griidc.pubs.jaxb.TitleInfo;
import edu.tamucc.hri.griidc.utils.PubsConstants;

/**
 * This class processes an XML file produced by the RefBaseWebService. It PRESUMES
 * and relies on there being ONE and ONLY ONE Publication represented in the
 * input file . Results are not valid if more than one only the code will
 * probably run without exception.
 * 
 * @author jvh
 * @see edu.tamucc.hri.griidc.utils.PubsConstants.
 */
public class PubsJaxbHandler {
	private JAXBContext jaxbContext = null;
	private Unmarshaller jaxbUnmarshaller = null;
	private SearchRetrieveResponse xmlRecord = null;
	private File file = null;
	private String fileName = null;

	private String doi = null;
	private String title = null;
	private String publisher = null;
	private String publicationDate = null;
	private String pubAbstract = null;
	private String[] authors = null;
	
	private Publication publication = null;

	public static boolean DeBug = false;

	public PubsJaxbHandler() {
	}

	public static boolean isDeBug() {
		return DeBug;
	}

	public static void setDeBug(boolean deBug) {
		DeBug = deBug;
	}

	public Publication processRefBaseXmlFile() throws JAXBException {
		return this.processRefBaseXmlFile(PubsConstants.RefBaseXmlFileName);
	}

	public Publication processRefBaseXmlFile(String fileName) throws JAXBException {
		this.init();
		this.fileName = fileName;
		if (isDeBug())
			System.out
					.println("PubsJaxbHandler.processRefBaseXmlFile() - file name: "
							+ this.fileName);
		this.file = new File(this.fileName);
		this.xmlRecord = (SearchRetrieveResponse) this.jaxbUnmarshaller
				.unmarshal(this.file);
		BigInteger n = this.xmlRecord.getNumberOfRecords();
		if (isDeBug())
			System.out.println("Number of records: " + n);
		Records recs = this.xmlRecord.getRecords();
		return this.parseMods(recs.getRecord().getRecordData().getMods());
	}

	public void init() {
		if (this.jaxbContext == null || this.jaxbUnmarshaller == null) {
			try {
				this.jaxbContext = JAXBContext
						.newInstance(SearchRetrieveResponse.class);
				this.jaxbUnmarshaller = jaxbContext.createUnmarshaller();

			} catch (JAXBException e) {
				// TODO Auto-generated catch block
				e.printStackTrace();
			}
		}
	}

	public static void main(String[] args) {
		PubsJaxbHandler pjh = new PubsJaxbHandler();
		PubsJaxbHandler.setDeBug(true);
		try {
			pjh.processRefBaseXmlFile();

		} catch (JAXBException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
	}

	/**
	 * process a set of Name to get each NamePart. Each Name obj contains
	 * 
	 * @param mods
	 */
	private void setAuthors(Mods mods) {
		if(mods == null) {
			return;
		} else if(mods.getName() == null) {
			return;
		}
		List<Name> nameSet = mods.getName();
		List<NamePart> namePartList = null;
		String lastName = null;
		String firstName = null;
		for (Name name : nameSet) {
			// an author name
			if (name.getType().equals(PubsConstants.NameTypePersonal)) {
				namePartList = name.getNamePart(); // get list of NamePart
				for (NamePart namePart : namePartList) {
					if (namePart.getType().equals(PubsConstants.NamePartTypeFamily)) {
						lastName = namePart.getValue().trim();
					} else if (namePart.getType().equals(
							PubsConstants.NamePartTypeGiven)) {
						firstName = namePart.getValue().trim();
					}
				}
				this.publication.addAuthor(new String(lastName + ", "
						+ firstName));
			}
		}
	}

	private void setTitle(Mods mods) {
		if(mods == null) {
			return;
		} else if(mods.getTitleInfo() == null) {
			return;
		} else if(mods.getTitleInfo().getTitle() == null) {
			return;
		} else if(mods.getTitleInfo().getTitle().trim() == null) {
			return;
		}
 		this.publication.setTitle(mods.getTitleInfo().getTitle().trim());
	}

	private void setPublisher(Mods mods) {
		if(mods == null) {
			return;
		} else if(mods.getRelatedItem() == null) {
			return;
		} else if(mods.getRelatedItem().getTitleInfo() == null) {
			return;
		}
		List<TitleInfo> titleInfoList = mods.getRelatedItem().getTitleInfo();
		if(isDeBug()) System.out.println("PubsJaxbHandler.setPublisher() titleInfoList size: " + titleInfoList.size());
		
		TitleInfo[] titleInfo = new TitleInfo[titleInfoList.size()];
		mods.getRelatedItem().getTitleInfo().toArray(titleInfo);
		for (TitleInfo ti : titleInfo) {
			this.publication.setPublisher(ti.getTitle());
		}	
	}
	
	private void setGeneres(Mods mods) {
		if(mods == null) {
			return;
		} else if(mods.getRelatedItem().getGenre() == null)  {
			return;
		}
		List<Genre> genreList = mods.getRelatedItem().getGenre();
		if(isDeBug()) System.out.println("PubsJaxbHandler.setPublisher() genreList size: " + genreList.size());
		
		Genre[] genres = new Genre[genreList.size()];
		genres = genreList.toArray(genres);
		StringBuffer sb = new StringBuffer();
		for(Genre genre : genres) {
			if(isDeBug()) System.out.println("PubsJaxbHandler.setPublisher() authority: " + genre.getAuthority());
			if(isDeBug()) System.out.println("PubsJaxbHandler.setPublisher() content: " + genre.getContent());
			if(sb.toString().length() > 0) sb.append(", ");
			sb.append(genre.getContent());
		}
	}

	private void setAbstract(Mods mods) {
		if(mods == null) {
			return;
		} else if(mods.getAbstract() == null)  {
			return;
		}
		this.publication.setAbstract(mods.getAbstract());
	}

	private void setPublicationDate(Mods mods) {
		if(mods == null) {
			return;
		} else if(mods.getOriginInfo() == null)  {
			return;
		} else if(mods.getOriginInfo().getDateIssued() == null)  {
			return;
		}
		String date = String.valueOf(mods.getOriginInfo().getDateIssued());
		this.publication.setPublicationYear(Integer.valueOf(date).intValue());
		
	}

	private void setDoi(Mods mods) {
		if(mods == null) {
			return;
		} else if(mods.getIdentifier() == null)  {
			return;
		}
		Identifier[] identifiers = new Identifier[1];
		identifiers = mods.getIdentifier().toArray(identifiers);
		for (Identifier id : identifiers) {
			if (id.getType().equals(PubsConstants.IdentifierTypeDoi)) {
				this.publication.setDoi(id.getValue().trim());
			}
		}
	}
	public String[] getAuthors() {
		return this.authors;
	}

	public String getTitle() {
		return this.title;
	}

	public String getPublisher() {
		return this.publisher;
	}

	public String getAbstract() {
		return this.pubAbstract;
	}

	public String getDoi() {
		return this.doi;
	}
	
	public String getPublicationDate() {
		return this.publicationDate;
	}

	public Publication getPublication() {
		return publication;
	}

	private Publication parseMods(Mods mods) {
		this.publication = new Publication();
		this.setAuthors(mods);
		this.setAbstract(mods);
		this.setDoi(mods);
		this.setPublisher(mods);
		this.setTitle(mods);
		this.setPublicationDate(mods);
		if (PubsJaxbHandler.isDeBug())
			System.out.println("PubsJaxbHandler.parseMods() \n"
					+ this.publication.toFormatedString());
		return this.publication;
	}
}