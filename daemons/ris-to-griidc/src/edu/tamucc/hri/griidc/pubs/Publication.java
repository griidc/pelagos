package edu.tamucc.hri.griidc.pubs;

import java.io.ByteArrayOutputStream;
import java.io.PrintStream;

import edu.tamucc.hri.griidc.utils.MiscUtils;
import edu.tamucc.hri.griidc.utils.StringList;
import edu.tamucc.hri.griidc.utils.ConfigurationConstants;

/**
 * This class is a secondary representation of the GRIIDC Publication table
 * record. It has an attribute for each of the columns in the table.
 * 
 * @author jvh
 * 
 */
/**
 * @author jvh
 *
 */
public class Publication implements Comparable {

	private String doi = null; // can be null
	private String title = null; // required
	private String publisher = null; // required
	private int publicationYear = ConfigurationConstants.Undefined; // required
	private String pubAbstract = null; // can be null
	private StringList authors = null; // required
	private int serialNumber = ConfigurationConstants.Undefined; // required
	private String topic = null; // required

	private StringBuffer errorMessageStringBuffer = null;
	public static final String AuthorSeparator = ConfigurationConstants.AuthorListSeparator;
	public static final String ErrorMsgSeparator = ConfigurationConstants.AuthorListSeparator;

	/**
	 * @param doi
	 * @param title
	 * @param publisher
	 * @param PublictionYear
	 * @param pubAbstract
	 * @param authors
	 * @param serialNumber
	 * @param topic
	 */
	public Publication(String doi, String title, String publisher,
			int publicationYear, String pubAbstract, StringList authors,
			int serialNumber, String topic) {
		super();
		this.doi = doi;
		this.title = title;
		this.publisher = publisher;
		this.publicationYear = publicationYear;
		this.pubAbstract = pubAbstract;
		this.authors = authors;
		this.serialNumber = serialNumber;
		this.topic = topic;
	}

	public Publication() {
		this.authors = new StringList();
		this.authors.setSeparator(AuthorSeparator);
	}

	public String getTitle() {
		return title;
	}

	public void setTitle(String title) {
		this.title = title;
	}

	public String getPublisher() {
		return publisher;
	}

	public void setPublisher(String publisher) {
		this.publisher = publisher;
	}

	public String getTopic() {
		return topic;
	}

	public void setTopic(String topic) {
		this.topic = topic;
	}

	public String getAbstract() {
		return pubAbstract;
	}

	public void setAbstract(String pubAbstract) {
		this.pubAbstract = pubAbstract;
	}

	public String getDoi() {
		return doi;
	}

	/**
	 * this function returns true if the state of this object is sufficient for
	 * it to be inserted into the GRIIDC Publication table and not fail for
	 * reasons due to missing data. It does not check on or report on
	 * referential integrity within the database. return false otherwise.
	 * 
	 * @return "RIS_Publication_Number" INTEGER NOT NULL, "Publication_Authors"
	 *         TEXT NOT NULL, "Publication_Year" Integer NOT NULL,
	 *         "Publication_JournalName" TEXT NOT NULL, "Publication_Title" TEXT
	 *         NOT NULL,
	 */
	public boolean isValidForGriidcDb() {
		this.errorMessageStringBuffer = null;
		boolean validStatus = true;
		if (this.authors == null || this.authors.size() == 0) {
			this.appendToErrorMessageBuffer("Authors are empty");
			validStatus = false;
		}
		if(this.publicationYear == ConfigurationConstants.Undefined) {
			this.appendToErrorMessageBuffer("Publication year is not set");
			validStatus = false;
		} else if (!(this.publicationYear >= ConfigurationConstants.EarliestPublicationYear && this.publicationYear <= ConfigurationConstants.LatestPublicationYear)) {
			this.appendToErrorMessageBuffer("Publication year: "
					+ this.getPublicationYear() + " is not within the range " + 
					ConfigurationConstants.EarliestPublicationYear + " to " +
					ConfigurationConstants.LatestPublicationYear);
			validStatus = false;
		}
		if (this.publisher == null || this.publisher.length() == 0) {
			this.appendToErrorMessageBuffer("Publisher is empty");
			validStatus = false;
		}
		if (this.title == null || this.title.length() == 0) {
			this.appendToErrorMessageBuffer("Title is empty");
			validStatus = false;
		}
		return validStatus;
	}

	private static final String PublicationIdFormat = "Publication: %s - ";

	private StringBuffer appendToErrorMessageBuffer(String msg) {
		String pubNum = "unknown";
		if (this.errorMessageStringBuffer == null) {
			this.errorMessageStringBuffer = new StringBuffer();
			if (this.getNumber() != ConfigurationConstants.Unknown)
				pubNum = String.valueOf(this.getNumber());
			this.errorMessageStringBuffer.append(MiscUtils.getFormattedString(
					PublicationIdFormat, pubNum));
		}
		this.errorMessageStringBuffer.append(msg);
		this.errorMessageStringBuffer.append(ErrorMsgSeparator);
		return errorMessageStringBuffer;
	}

	public String getGriidcDBValidityErrorMessage() {
		String msg = null;
		if (this.isValidForGriidcDb()) {
			msg = "No Errors in publication: " + this.getNumber();
		} else {
			msg = this.errorMessageStringBuffer.toString();
			int ndx = msg.lastIndexOf(ErrorMsgSeparator.trim());
			if (ndx != -1)
				msg = msg.substring(0, ndx);
		}
		return msg;
	}

	public void setDoi(String doi) {
		this.doi = doi;
	}

	public int getSerialNumber() {
		return serialNumber;
	}

	public void setNumber(int serNum) {
		this.setSerialNumber(serNum);
	}

	public int getNumber() {
		return this.getSerialNumber();
	}

	public void setSerialNumber(int serNum) {
		this.serialNumber = serNum;
	}

	public String getFirstAuthor() {
		if (this.authors == null)
			return null;
		return this.getAuthors()[0];
	}

	public String[] getAuthors() {
		if (this.authors == null)
			return null;
		return this.authors.toArray();
	}

	public String getAuthorsString() {
		return this.authors.toString();
	}

	public String addAuthor(String author) {
		this.authors.addItem(author);
		return author;
	}

	public int setAuthor(String authors) {
		String[] authorArray = authors.split(this.AuthorSeparator);
		for (String a : authorArray) {
			this.authors.addItem(a);
		}
		return this.getAuthors().length;
	}

	public int getPublicationYear() {
		return publicationYear;
	}

	public void setPublicationYear(int publicationYear) {
		this.publicationYear = publicationYear;
	}

	public String getPubAbstract() {
		return pubAbstract;
	}

	public void setPubAbstract(String pubAbstract) {
		this.pubAbstract = pubAbstract;
	}

	@Override
	public String toString() {
		return this.toFormatedString();
	}

	@Override
	public int hashCode() {
		final int prime = 31;
		int result = 1;
		result = prime * result + ((authors == null) ? 0 : authors.hashCode());
		result = prime * result + ((doi == null) ? 0 : doi.hashCode());
		result = prime * result
				+ ((pubAbstract == null) ? 0 : pubAbstract.hashCode());
		result = prime * result + publicationYear;
		result = prime * result
				+ ((publisher == null) ? 0 : publisher.hashCode());
		result = prime * result + serialNumber;
		result = prime * result + ((title == null) ? 0 : title.hashCode());
		result = prime * result + ((topic == null) ? 0 : topic.hashCode());
		return result;
	}

	@Override
	public boolean equals(Object obj) {
		if (this == obj) // self same object
			return true;
		if (obj == null) // "this" is not null
			return false;
		if (getClass() != obj.getClass())  // not the same class
			return false;
		
		Publication other = (Publication) obj;
		if (!areAuthorsEqual(other))
			return false;
		
		if (doi == null) {
			if (other.doi != null)
				return false;
		} else if (!doi.equals(other.doi))
			return false;
		
		if (pubAbstract == null) {
			if (other.pubAbstract != null)
				return false;
		} else if (!pubAbstract.equals(other.pubAbstract))
			return false;
		
		if (publicationYear != other.publicationYear)
			return false;
		
		if (publisher == null) {
			if (other.publisher != null)
				return false;
		} else if (!publisher.equals(other.publisher))
			return false;
		
		if (serialNumber != other.serialNumber)
			return false;
		
		if (title == null) {
			if (other.title != null)
				return false;
		} else if (!title.equals(other.title))
			return false;
		
		if (topic == null) {
			if (other.topic != null)
				return false;
		} else if (!topic.equals(other.topic))
			return false;
		
		return true;
	}

	
	/**
	 * @param other
	 * @return
	 */
	private boolean areAuthorsEqual(Publication other) {

		if (this.authors == null && other.authors == null) // both null - equal
			return true;
		if (this.authors == null || other.authors == null) // one is null
			return false;

		String[] otherAuthors = other.getAuthors();
		String[] thisAuthors = this.getAuthors();
		if (otherAuthors.length != thisAuthors.length)  // not equal if not the same number of authors
			return false;
		for (int i = 0; i < thisAuthors.length; i++) {  // if any author is not equal, the authors are not equal
			if (!thisAuthors[i].equals(otherAuthors[i]))
				return false;
		}
		return true;
	}

	public String toFormatedString() {
		String format = "%15s: %-50s%n";
		StringBuffer sb = new StringBuffer();
		ByteArrayOutputStream outStream = new ByteArrayOutputStream();
		PrintStream ps = new PrintStream(outStream);

		ps.printf(format, "doi", this.getDoi());
		ps.printf(format, "title", this.getTitle());
		ps.printf(format, "publisher", this.getPublisher());
		ps.printf(format, "year", this.getPublicationYear());
		ps.printf(format, "abstract", this.getAbstract());
		ps.printf(format, "first author", this.getFirstAuthor());
		ps.printf(format, "authors", (this.authors == null) ? "null"
				: this.authors.toString());
		ps.printf(format, "serial number",
				String.valueOf(this.getSerialNumber()));
		return outStream.toString();
	}
	public String toShortString() {
		String bigFormat = "%15s: %-5s, %5s: %-30s";
		StringBuffer sb = new StringBuffer();
		ByteArrayOutputStream outStream = new ByteArrayOutputStream();
		PrintStream ps = new PrintStream(outStream);

		ps.printf(bigFormat, "serial number",
				String.valueOf(this.getSerialNumber()),  "doi", this.getDoi());
		return outStream.toString();
	}

	public static String[] a1s = { "Holland, Joe V.", "Showalter, L. M.",
			"Ellis, Sandra" };

	public static void main(String[] args) {
		StringList authors1 = new StringList(a1s);

		Publication dogPub1 = new Publication("XyZzy-2014",
				"Life of the Dogfish", "Dog Fish Monthly", 2012,
				"Borring stuff about a fish", authors1, 1234, "Dog Fish");
		Publication dogPub2 = new Publication("XyZzy-2014",
				"Life of the Dogfish", "Dog Fish Monthly", 2010,
				"Borring stuff about a fish", authors1, 2345, "Dog Fish");
		Publication dogPub3 = new Publication("XyZzy-2014",
				"Life of the Dogfish", "Dog Fish Monthly", 1941,
				"Borring stuff about a fish", null, 3456, "Dog Fish");
		Publication catPub = new Publication("KatKat-2012", null, null, -1,
				null, null, 4567, null);

		Publication[] pubs = { dogPub1, dogPub2, dogPub3, catPub };
		for (Publication p1 : pubs) {
			System.out.println(p1.toFormatedString());
		}
		for (Publication p1 : pubs) {
			for (Publication p2 : pubs) {
				compareEm(p1, p2);
			}
		}
		for (Publication p1 : pubs) {
			System.out.println("\nPublication: " + p1.toFormatedString());
			System.out.println(p1.getGriidcDBValidityErrorMessage());
		}

	}

	public static void compareEm(Publication p1, Publication p2) {
		System.out.println("Comparing: \n\t" + p1 + "\n To \n\t" + p2);
		if (p1.equals(p2))
			System.out.println("\n***********\nEQUAl\n************");
		else
			System.out.println("\n<><><><><>\n NOT EQUAl\n<><><><><>");
	}

	@Override
	public int compareTo(Object arg0) {
		if(this.equals(arg0)) return 0;
		Publication oP = (Publication) arg0;
		return this.getSerialNumber() - oP.getSerialNumber();
	}

}
